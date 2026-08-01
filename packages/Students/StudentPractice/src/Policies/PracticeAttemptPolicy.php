<?php

namespace Mindigo\StudentPractice\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeAttempt;

class PracticeAttemptPolicy
{
    public function view(User $user, PracticeAttempt $attempt): bool
    {
        return $user->isAdmin() || (int) $attempt->student_id === (int) $user->getAuthIdentifier();
    }

    public function update(User $user, PracticeAttempt $attempt): bool
    {
        return $this->view($user, $attempt) && ! $attempt->isCompleted();
    }

    public function complete(User $user, PracticeAttempt $attempt): bool
    {
        return $this->update($user, $attempt);
    }
}
