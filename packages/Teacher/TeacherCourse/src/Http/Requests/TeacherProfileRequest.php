<?php

namespace Mindigo\TeacherCourse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mindigo\TeacherCourse\Models\TeacherProfile;

class TeacherProfileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['is_public' => $this->boolean('is_public')]);
    }

    public function authorize(): bool
    {
        $profile = $this->route('profile');

        return $profile instanceof TeacherProfile && ($this->user()?->can('update', $profile) ?? false);
    }

    public function rules(): array
    {
        return ['headline' => ['nullable', 'string', 'max:180'], 'biography' => ['nullable', 'string', 'max:5000'], 'specialization' => ['nullable', 'string', 'max:255'], 'experience_years' => ['required', 'integer', 'min:0', 'max:80'], 'qualifications' => ['nullable', 'string', 'max:3000'], 'is_public' => ['required', 'boolean']];
    }
}
