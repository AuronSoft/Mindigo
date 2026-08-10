<?php

namespace Mindigo\TeacherCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;

class TeacherCalendarIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'teacher';
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
            'classroom_id' => ['nullable', 'integer'],
            'kinds' => ['nullable', 'array'],
            'kinds.*' => ['string', Rule::enum(CalendarEventKind::class)],
        ];
    }
}
