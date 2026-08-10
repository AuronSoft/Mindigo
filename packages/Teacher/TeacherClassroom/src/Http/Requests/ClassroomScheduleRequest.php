<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class ClassroomScheduleRequest extends FormRequest
{
    private const COURSE_DAY_CODES = [
        1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ClassroomSchedule::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'session_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'description' => ['nullable', 'string', 'max:1000'],
            'makeup_reason' => ['nullable', 'required_if:type,'.ClassroomSchedule::TYPE_MAKEUP, 'string', 'min:10', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $classroom = $this->classroom();
            if (! $classroom) {
                return;
            }

            $schedule = $this->route('schedule');
            $duplicate = ClassroomSchedule::query()
                ->where('classroom_id', $classroom->id)
                ->whereDate('session_date', $this->string('session_date'))
                ->where('start_time', $this->string('start_time').':00')
                ->when($schedule instanceof ClassroomSchedule, fn ($query) => $query->whereKeyNot($schedule->id))
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('start_time', __('teacher-classroom::app.schedule_slot_exists'));
            }

            if ($classroom->type !== Classroom::TYPE_COURSE || ! $classroom->course_id) {
                return;
            }

            $course = $classroom->course()->first();
            if (! $course?->starts_at || ! $course->schedule_days || ! $course->study_time) {
                $validator->errors()->add('session_date', __('teacher-classroom::app.course_schedule_incomplete'));

                return;
            }

            $sessionDate = Carbon::createFromFormat('Y-m-d', $this->string('session_date'));
            if ($sessionDate->startOfDay()->lt($course->starts_at->copy()->startOfDay())) {
                $validator->errors()->add('session_date', __('teacher-classroom::app.session_before_course_start', ['date' => $course->starts_at->format('d/m/Y')]));
            }

            if ($this->string('type')->toString() === ClassroomSchedule::TYPE_MAKEUP) {
                return;
            }

            $dayCode = self::COURSE_DAY_CODES[$sessionDate->dayOfWeekIso];
            if (! in_array($dayCode, $course->schedule_days, true)) {
                $validator->errors()->add('session_date', __('teacher-classroom::app.session_wrong_course_day'));
            }

            if (preg_match('/^(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})$/', $course->study_time, $matches)) {
                if ($this->string('start_time')->toString() !== $matches[1] || $this->string('end_time')->toString() !== $matches[2]) {
                    $validator->errors()->add('start_time', __('teacher-classroom::app.session_wrong_course_time', ['time' => $course->study_time]));
                }
            }
        });
    }

    private function classroom(): ?Classroom
    {
        $classroom = $this->route('classroom');
        if ($classroom instanceof Classroom) {
            return $classroom;
        }

        $schedule = $this->route('schedule');

        return $schedule instanceof ClassroomSchedule ? $schedule->classroom : null;
    }
}
