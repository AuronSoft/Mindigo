<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\CourseReview;
use Mindigo\TeacherCourse\Models\TeacherProfile;

class TeacherProfileService
{
    public function publicProfile(int $teacherId): array
    {
        $teacher = User::query()->whereKey($teacherId)->where('role', 'teacher')->with('teacherProfile')->firstOrFail();
        abort_unless($teacher->teacherProfile?->is_public, 404);
        $courses = $teacher->taughtCourses()->publiclyListed()->with(['teacher:id,name', 'subject:id,name', 'category:id,name'])->withCount('lessons')->latest('published_at')->paginate(12);
        $rating = CourseReview::query()->visible()->whereHas('course', fn ($query) => $query->where('teacher_id', $teacher->id)->publiclyListed())->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as average')->first();

        return ['teacher' => $teacher, 'profile' => $teacher->teacherProfile, 'courses' => $courses, 'studentCount' => $teacher->taughtCourses()->publiclyListed()->sum('enrollment_count'), 'ratingCount' => (int) $rating->total, 'ratingAverage' => round((float) $rating->average, 1)];
    }

    public function editable(User $teacher): TeacherProfile
    {
        return TeacherProfile::query()->firstOrCreate(['user_id' => $teacher->id]);
    }

    public function update(TeacherProfile $profile, array $data): TeacherProfile
    {
        return DB::transaction(function () use ($profile, $data): TeacherProfile {
            $data['qualifications'] = collect(preg_split('/\r\n|\r|\n/', $data['qualifications'] ?? ''))->map(fn (string $item) => trim($item))->filter()->values()->all() ?: null;
            $profile->update($data);

            return $profile->refresh();
        });
    }
}
