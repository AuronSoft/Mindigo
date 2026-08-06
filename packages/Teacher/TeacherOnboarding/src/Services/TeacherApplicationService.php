<?php

namespace Mindigo\TeacherOnboarding\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\Notification\Notifications\TeacherApplicationSubmitted;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseCategory;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class TeacherApplicationService
{
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
