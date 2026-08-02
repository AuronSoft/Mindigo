<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\CourseEnrollment;

class CourseEnrollmentPolicy
{
    public function view(User $user, CourseEnrollment $enrollment): bool
    {
        return $user->isStudent() && (int) $enrollment->student_id === (int) $user->getAuthIdentifier();
    }

    public function update(User $user, CourseEnrollment $enrollment): bool
    {
        return $this->view($user, $enrollment)
            && in_array($enrollment->status, CourseEnrollment::ACTIVE_STATUSES, true);
    }
}
