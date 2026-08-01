<?php

namespace Mindigo\StudentPractice\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeLearningInsight;

class PracticeLearningInsightPolicy
{
    public function view(User $user, PracticeLearningInsight $insight): bool
    {
        return $user->isAdmin() || (int) $insight->student_id === (int) $user->getAuthIdentifier();
    }
}
