<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\Lesson;

class LessonPolicy
{
    public function view(User $user, Lesson $lesson): bool
    {
        return $user->can('view', $lesson->chapter->course);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['teacher', 'admin']);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('update', $lesson->chapter->course);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}
