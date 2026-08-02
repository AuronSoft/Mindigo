<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Course;

class CourseWishlistPolicy
{
    public function manage(User $user, Course $course): bool
    {
        return $user->isStudent() && $course->isPublished();
    }
}
