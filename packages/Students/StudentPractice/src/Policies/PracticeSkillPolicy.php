<?php

namespace Mindigo\StudentPractice\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeSkill;

class PracticeSkillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'admin']);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, PracticeSkill $skill): bool
    {
        return $user->isAdmin() || (int) $skill->created_by === (int) $user->getAuthIdentifier();
    }

    public function delete(User $user, PracticeSkill $skill): bool
    {
        return $this->update($user, $skill);
    }
}
