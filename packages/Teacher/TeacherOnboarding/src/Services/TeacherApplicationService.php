<?php

namespace Mindigo\TeacherOnboarding\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\Notification\Notifications\TeacherApplicationDecision;
use Mindigo\Notification\Notifications\TeacherApplicationSubmitted;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class TeacherApplicationService
{
    public const TRANSITIONS = [
        TeacherApplication::STATUS_SUBMITTED => [
            TeacherApplication::STATUS_SCREENING,
            TeacherApplication::STATUS_REJECTED,
        ],
        TeacherApplication::STATUS_SCREENING => [
            TeacherApplication::STATUS_NEED_MORE_INFO,
            TeacherApplication::STATUS_REJECTED,
        ],
    ];

    public function options(): array
    {
        return [
            'subjects' => Subject::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'categories' => CourseCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
            'applicationTypes' => TeacherApplication::APPLICATION_TYPES,
            'teachingModes' => TeacherApplication::TEACHING_MODES,
            'educationLevels' => Course::EDUCATION_LEVELS,
            'genders' => array_keys(User::GENDERS),
        ];
    }

    public function submit(?User $user, array $data, array $files = []): TeacherApplication
    {
        $this->ensureCanSubmit($user, $data['email']);

        return DB::transaction(function () use ($user, $data, $files): TeacherApplication {
            $documents = $this->storeDocuments($files);

            $application = TeacherApplication::query()->create([
                ...$data,
                'user_id' => $user?->getAuthIdentifier(),
                'application_code' => $this->generateCode(),
                'status' => TeacherApplication::STATUS_SUBMITTED,
                'verification_documents' => $documents,
                'submitted_at' => now(),
            ]);

            DB::afterCommit(function () use ($application): void {
                $admins = User::query()->admins()->active()->get();

                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new TeacherApplicationSubmitted(
                        $application->id,
                        $application->application_code,
                        $application->full_name,
                        $application->application_type,
                    ));
                }
            });

            return $application;
        });
    }

    public function queue(array $filters): LengthAwarePaginator
    {
        return TeacherApplication::query()
            ->with(['user:id,name,email', 'subject:id,name', 'category:id,name', 'reviewer:id,name'])
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters): void {
                $search = '%'.trim($filters['search']).'%';
                $query->where(fn ($nested) => $nested
                    ->where('application_code', 'like', $search)
                    ->orWhere('full_name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('specialization', 'like', $search));
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['application_type'] ?? null), fn ($query) => $query->where('application_type', $filters['application_type']))
            ->when(($filters['sort'] ?? 'newest') === 'oldest', fn ($query) => $query->orderBy('submitted_at'))
            ->when(($filters['sort'] ?? 'newest') === 'name', fn ($query) => $query->orderBy('full_name'))
            ->when(($filters['sort'] ?? 'newest') === 'newest', fn ($query) => $query->orderByDesc('submitted_at'))
            ->paginate(15)
            ->withQueryString();
    }

    public function reviewableStatuses(): array
    {
        return [
            TeacherApplication::STATUS_SUBMITTED,
            TeacherApplication::STATUS_SCREENING,
            TeacherApplication::STATUS_NEED_MORE_INFO,
            TeacherApplication::STATUS_REJECTED,
        ];
    }

    public function detail(TeacherApplication $application): TeacherApplication
    {
        return $application->load([
            'user:id,name,email',
            'subject:id,name',
            'category:id,name',
            'reviewer:id,name',
            'provisioner:id,name',
            'latestInterview.interviewer:id,name',
        ]);
    }

    public function nextStatuses(TeacherApplication $application): array
    {
        return self::TRANSITIONS[$application->status] ?? [];
    }

    public function transition(TeacherApplication $application, User $admin, array $data): TeacherApplication
    {
        $targetStatus = $data['status'];
        $this->ensureTransitionAllowed($application, $targetStatus);

        return DB::transaction(function () use ($application, $admin, $data, $targetStatus): TeacherApplication {
            $application = TeacherApplication::query()
                ->whereKey($application->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureTransitionAllowed($application, $targetStatus);

            $application->forceFill([
                'status' => $targetStatus,
                'reviewed_by' => $admin->getAuthIdentifier(),
                'reviewed_at' => now(),
                'internal_note' => $data['internal_note'] ?? $application->internal_note,
                'status_note' => $data['status_note'] ?? null,
            ])->save();

            if (in_array($targetStatus, [TeacherApplication::STATUS_NEED_MORE_INFO, TeacherApplication::STATUS_REJECTED], true)) {
                DB::afterCommit(fn () => $this->notifyApplicant($application, $targetStatus, $data['status_note'] ?? null));
            }

            return $application->refresh();
        });
    }

    public function document(TeacherApplication $application, string $document): array
    {
        $documents = $application->verification_documents ?? [];
        $metadata = $documents[$document] ?? null;

        abort_if(! is_array($metadata) || empty($metadata['path']), 404);

        $disk = $metadata['disk'] ?? 'local';
        abort_unless(Storage::disk($disk)->exists($metadata['path']), 404);

        return [
            'path' => $metadata['path'],
            'name' => $metadata['name'] ?? basename($metadata['path']),
            'disk' => $disk,
        ];
    }

    public function summary(): array
    {
        return [
            'submitted' => TeacherApplication::query()->where('status', TeacherApplication::STATUS_SUBMITTED)->count(),
            'screening' => TeacherApplication::query()->where('status', TeacherApplication::STATUS_SCREENING)->count(),
            'need_more_info' => TeacherApplication::query()->where('status', TeacherApplication::STATUS_NEED_MORE_INFO)->count(),
            'rejected' => TeacherApplication::query()->where('status', TeacherApplication::STATUS_REJECTED)->count(),
        ];
    }

    public function applicants(): Collection
    {
        return TeacherApplication::query()
            ->select('application_type')
            ->selectRaw('count(*) as total')
            ->groupBy('application_type')
            ->pluck('total', 'application_type');
    }

    private function ensureCanSubmit(?User $user, string $email): void
    {
        if ($user?->isTeacher()) {
            throw new AuthorizationException(__('teacher-onboarding::application.errors.teacher_not_allowed'));
        }

        $hasActiveApplication = TeacherApplication::query()
            ->activeReview()
            ->where(function ($query) use ($user, $email): void {
                $query->where('email', $email);

                if ($user) {
                    $query->orWhere('user_id', $user->getAuthIdentifier());
                }
            })
            ->exists();

        if ($hasActiveApplication) {
            throw new AuthorizationException(__('teacher-onboarding::application.errors.duplicate_application'));
        }
    }

    private function ensureTransitionAllowed(TeacherApplication $application, string $targetStatus): void
    {
        if (! in_array($targetStatus, self::TRANSITIONS[$application->status] ?? [], true)) {
            throw new AuthorizationException(__('teacher-onboarding::admin.invalid_transition'));
        }
    }

    private function notifyApplicant(TeacherApplication $application, string $status, ?string $note): void
    {
        $applicant = $application->user;

        if ($applicant) {
            $applicant->notify(new TeacherApplicationDecision($application->application_code, $status, $note));
        }
    }

    private function storeDocuments(array $files): array
    {
        $documents = [];

        foreach ($files as $type => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $documents[$type] = [
                'disk' => 'local',
                'path' => $file->store('teacher-applications/'.now()->format('Y/m'), 'local'),
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $documents;
    }

    private function generateCode(): string
    {
        do {
            $code = 'TA-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (TeacherApplication::query()->where('application_code', $code)->exists());

        return $code;
    }
}
