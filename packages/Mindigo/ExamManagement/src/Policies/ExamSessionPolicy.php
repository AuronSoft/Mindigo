<?php

namespace Mindigo\ExamManagement\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamSession;

class ExamSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTeacher();
    }

    public function view(User $user, ExamSession $session): bool
    {
        return $user->isTeacher() && (int) $session->organizer_id === (int) $user->getAuthIdentifier();
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, ExamSession $session): bool
    {
        return $this->view($user, $session) && $session->isMutable();
    }

    public function manage(User $user, ExamSession $session): bool
    {
        return $this->view($user, $session);
    }
}
