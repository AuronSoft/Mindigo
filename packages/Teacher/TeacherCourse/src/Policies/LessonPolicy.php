<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\Lesson;

class LessonPolicy
{
    public function view(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course();

        return $user->can('view', $course)
            || ($lesson->is_preview && $user->can('viewDetail', $course))
            || ($user->isStudent() && CourseEnrollment::query()
                ->where('course_id', $course->id)
                ->where('student_id', $user->id)
                ->whereIn('status', CourseEnrollment::ACTIVE_STATUSES)
                ->availableToStudent()
                ->whereHas('course', fn ($query) => $query
                    ->where('is_active', true)
                    ->where('publication_status', '!=', Course::PUBLICATION_ARCHIVED))
                ->exists());
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'admin']);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->chapter->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}
