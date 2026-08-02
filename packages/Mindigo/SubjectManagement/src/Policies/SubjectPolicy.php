<?php

namespace Mindigo\SubjectManagement\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('subjects.view');
    }

    public function view(User $user, Subject $subject): bool
    {
        return $user->hasPermissionTo('subjects.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('subjects.create');
    }

    public function update(User $user, Subject $subject): bool
    {
        return $user->hasPermissionTo('subjects.update');
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->hasPermissionTo('subjects.delete');
    }
}
