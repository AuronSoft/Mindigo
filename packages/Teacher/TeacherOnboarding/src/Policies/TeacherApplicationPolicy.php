<?php

namespace Mindigo\TeacherOnboarding\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherOnboarding\Models\TeacherApplication;

class TeacherApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, TeacherApplication $application): bool
    {
        return $user->isAdmin();
    }

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

    public function update(User $user, TeacherApplication $application): bool
    {
        return $user->isAdmin();
    }

    public function viewDocument(User $user, TeacherApplication $application): bool
    {
        return $this->view($user, $application);
    }
}
