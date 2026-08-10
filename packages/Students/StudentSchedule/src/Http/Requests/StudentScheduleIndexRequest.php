<?php

namespace Mindigo\StudentSchedule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\AcademicCalendar\Enums\CalendarEventKind;

class StudentScheduleIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'student';
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
            'view' => ['nullable', Rule::in(['today', 'week', 'month', 'schedule'])],
            'kinds' => ['nullable', 'array'],
            'kinds.*' => ['string', Rule::enum(CalendarEventKind::class)],
        ];
    }
}
