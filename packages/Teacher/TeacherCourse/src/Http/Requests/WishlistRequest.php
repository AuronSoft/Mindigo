<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course
            ? ($this->user()?->can('manageWishlist', $course) ?? false)
            : ($this->user()?->isStudent() ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
