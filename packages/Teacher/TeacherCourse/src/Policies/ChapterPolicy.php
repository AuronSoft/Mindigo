<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Chapter;

class ChapterPolicy
{
    public function view(User $user, Chapter $chapter): bool
    {
        return $user->can('view', $chapter->course);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'admin']);
    }

    public function update(User $user, Chapter $chapter): bool
    {
        return $user->can('update', $chapter->course);
    }

    public function delete(User $user, Chapter $chapter): bool
    {
        return $this->update($user, $chapter);
    }
}
