<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'admin']);
    }

    public function view(User $user, Course $course): bool
    {
        return $user->isAdmin() || (int) $course->teacher_id === (int) $user->getAuthIdentifier();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'admin']);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->view($user, $course) && $course->publication_status !== Course::PUBLICATION_ARCHIVED;
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->view($user, $course);
    }

    public function submitForReview(User $user, Course $course): bool
    {
        return $this->update($user, $course)
            && in_array($course->publication_status, [Course::PUBLICATION_DRAFT, Course::PUBLICATION_UNLISTED], true);
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->isAdmin() && $course->publication_status === Course::PUBLICATION_PENDING_REVIEW;
    }

    public function archive(User $user, Course $course): bool
    {
        return $this->view($user, $course) && $course->publication_status !== Course::PUBLICATION_ARCHIVED;
    }

    public function withdrawReview(User $user, Course $course): bool
    {
        return $this->view($user, $course) && $course->publication_status === Course::PUBLICATION_PENDING_REVIEW;
    }
}
