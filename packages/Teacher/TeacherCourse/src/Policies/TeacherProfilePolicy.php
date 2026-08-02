<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\TeacherProfile;

class TeacherProfilePolicy
{
    public function view(User $user, TeacherProfile $profile): bool
    {
        return $profile->is_public || $user->isAdmin() || (int) $profile->user_id === (int) $user->getAuthIdentifier();
    }

    public function update(User $user, TeacherProfile $profile): bool
    {
        return $user->isAdmin() || (int) $profile->user_id === (int) $user->getAuthIdentifier();
    }
}
