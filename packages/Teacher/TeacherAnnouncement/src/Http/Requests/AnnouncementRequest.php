<?php

namespace Mindigo\TeacherAnnouncement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\TeacherAnnouncement\Models\Announcement;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:10000'],
            'type' => ['required', Rule::in(Announcement::TYPES)],
            'is_pinned' => ['nullable', 'boolean'],
            'classroom_ids' => ['nullable', 'array'],
            'classroom_ids.*' => ['exists:classrooms,id'],
        ];
    }
}
