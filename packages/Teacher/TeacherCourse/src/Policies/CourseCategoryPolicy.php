<?php

namespace Mindigo\TeacherCourse\Policies;

use Mindigo\Auth\Models\User;
use Mindigo\TeacherCourse\Models\CourseCategory;

class CourseCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('course-categories.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('course-categories.create');
    }

    public function update(User $user, CourseCategory $category): bool
    {
        return $user->hasPermissionTo('course-categories.update');
    }

    public function delete(User $user, CourseCategory $category): bool
    {
        return $user->hasPermissionTo('course-categories.delete');
    }
}
