<?php

namespace Mindigo\StudentPractice\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeSet;

class PracticeSetPolicy
{
    public function view(User $user, PracticeSet $set): bool
    {
        if ($user->isAdmin() || (int) $set->creator_id === (int) $user->getAuthIdentifier()) {
            return true;
        }

        return $user->isStudent()
            && $set->classroom_id !== null
            && $set->classroom()->whereHas(
                'students',
                fn ($query) => $query->whereKey($user->getAuthIdentifier())
            )->exists();
    }

    public function start(User $user, PracticeSet $set): bool
    {
        return $user->isStudent()
            && $set->status === PracticeSet::STATUS_READY
            && $this->view($user, $set);
    }

    public function delete(User $user, PracticeSet $set): bool
    {
        return $user->isAdmin() || (int) $set->creator_id === (int) $user->getAuthIdentifier();
    }
}
