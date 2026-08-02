<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\CourseReview;

class CourseReviewPolicy
{
    public function update(User $user, CourseReview $review): bool
    {
        return $user->isStudent() && (int) $review->student_id === (int) $user->id;
    }

    public function reply(User $user, CourseReview $review): bool
    {
        return $user->isAdmin() || (int) $review->course->teacher_id === (int) $user->id;
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
