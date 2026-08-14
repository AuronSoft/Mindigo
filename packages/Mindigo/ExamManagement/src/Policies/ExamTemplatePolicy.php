<?php

namespace Mindigo\ExamManagement\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamTemplate;

class ExamTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTeacher();
    }

    public function view(User $user, ExamTemplate $template): bool
    {
        return $user->isTeacher() && (int) $template->owner_id === (int) $user->getAuthIdentifier();
    }

    public function create(User $user): bool
    {
        return $user->isTeacher();
    }

    public function update(User $user, ExamTemplate $template): bool
    {
        return $this->view($user, $template) && $template->isEditable();
    }

    public function delete(User $user, ExamTemplate $template): bool
    {
        return $this->view($user, $template) && ! $template->sessions()->exists();
    }
}
