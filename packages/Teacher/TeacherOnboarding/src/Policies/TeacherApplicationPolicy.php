<?php

namespace Mindigo\TeacherOnboarding\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class TeacherApplicationPolicy
{
    public function create(?User $user): bool
    {
        if ($user?->isTeacher()) {
            return false;
        }

        if (! $user) {
            return true;
        }

        return ! TeacherApplication::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->activeReview()
            ->exists();
    }
}
