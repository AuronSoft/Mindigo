<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Support\Facades\DB;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\CourseReview;
use Mindigo\TeacherCourse\Models\TeacherProfile;

class TeacherProfileService
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function directory(array $filters): array
    {
        $teachers = $this->publicTeacherQuery()
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters): void {
                $search = trim((string) $filters['search']);
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('teacherProfile', fn ($profile) => $profile->where('headline', 'like', "%{$search}%")->orWhere('specialization', 'like', "%{$search}%"));
                });
            })
            ->when(filled($filters['specialization'] ?? null), fn ($query) => $query->whereHas('teacherProfile', fn ($profile) => $profile->where('specialization', $filters['specialization'])))
            ->withCount(['taughtCourses as public_courses_count' => fn ($query) => $query->publiclyListed()])
            ->orderByDesc('public_courses_count')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $specializations = TeacherProfile::query()
            ->where('is_public', true)
            ->whereNotNull('specialization')
            ->whereHas('user', fn ($query) => $query->whereHas('approvedTeacherApplication'))
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization');

        return ['teachers' => $teachers, 'specializations' => $specializations, 'filters' => $filters];
    }

    public function publicProfile(int $teacherId): array
    {
        $teacher = $this->publicTeacherQuery()->whereKey($teacherId)->firstOrFail();
        $courses = $teacher->taughtCourses()->publiclyListed()->with(['teacher:id,name,avatar', 'teacher.teacherProfile:id,user_id,headline,is_public', 'teacher.approvedTeacherApplication:id,user_id', 'subject:id,name', 'category:id,name'])->withCount('lessons')->latest('published_at')->paginate(12);
        $rating = CourseReview::query()->visible()->whereHas('course', fn ($query) => $query->where('teacher_id', $teacher->id)->publiclyListed())->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')->first();

        return ['teacher' => $teacher, 'profile' => $teacher->teacherProfile, 'courses' => $courses, 'courseCount' => $courses->total(), 'studentCount' => $teacher->taughtCourses()->publiclyListed()->sum('enrollment_count'), 'ratingCount' => (int) $rating->total, 'ratingAverage' => round((float) $rating->average, 1)];
    }

    public function editable(User $teacher): TeacherProfile
    {
        return TeacherProfile::query()->firstOrCreate(['user_id' => $teacher->id]);
    }

    public function update(TeacherProfile $profile, array $data): TeacherProfile
    {
        return DB::transaction(function () use ($profile, $data): TeacherProfile {
            $data['qualifications'] = collect(preg_split('/\r\n|\r|\n/', $data['qualifications'] ?? ''))->map(fn (string $item) => trim($item))->filter()->values()->all() ?: null;
            $data['social_links'] = collect($data['social_links'] ?? [])->map(fn (?string $url) => filled($url) ? trim((string) $url) : null)->filter()->all() ?: null;
            $oldValues = $profile->getAttributes();
            $profile->update($data);

            $this->audit->record(
                'teacher_public_profile_updated',
                'teacher-onboarding',
                oldValues: $oldValues,
                newValues: $profile->getChanges(),
                auditable: $profile,
                user: $profile->user,
            );

            return $profile->refresh();
        });
    }

    private function publicTeacherQuery()
    {
        return User::query()
            ->where('role', 'teacher')
            ->with(['teacherProfile', 'approvedTeacherApplication:id,user_id'])
            ->whereHas('teacherProfile', fn ($query) => $query->where('is_public', true))
            ->whereHas('approvedTeacherApplication');
    }
}
