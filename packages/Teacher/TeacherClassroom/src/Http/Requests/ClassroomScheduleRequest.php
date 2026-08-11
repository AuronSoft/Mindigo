<?php

namespace Mindigo\TeacherClassroom\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherCourse\Models\Lesson;

class ClassroomScheduleRequest extends FormRequest
{
    private const COURSE_DAY_CODES = [
        1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $classroom = $this->classroom();

        $this->merge([
            'delivery_mode' => $this->input('delivery_mode', ClassroomSchedule::DELIVERY_OFFLINE),
            'status' => $this->input('status', ClassroomSchedule::STATUS_SCHEDULED),
        ]);

        // Standalone classes own their calendar. Session classification only has
        // meaning when a class inherits a fixed schedule from a linked course.
        if ($classroom && ($classroom->type !== Classroom::TYPE_COURSE || ! $classroom->course_id)) {
            $this->merge([
                'type' => ClassroomSchedule::TYPE_REGULAR,
                'makeup_reason' => null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ClassroomSchedule::TYPES)],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'delivery_mode' => ['required', Rule::in(ClassroomSchedule::DELIVERY_MODES)],
            'status' => ['required', Rule::in(ClassroomSchedule::STATUSES)],
            'title' => ['required', 'string', 'max:255'],
            'session_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url:http,https', 'max:2048'],
            'makeup_reason' => ['nullable', 'required_if:type,'.ClassroomSchedule::TYPE_MAKEUP, 'string', 'min:10', 'max:1000'],
            'cancel_reason' => ['nullable', 'required_if:status,'.ClassroomSchedule::STATUS_CANCELLED, 'string', 'min:10', 'max:1000'],
            'reschedule_reason' => [Rule::requiredIf($this->routeIs('teacher.calendar.sessions.reschedule')), 'nullable', 'string', 'min:10', 'max:1000'],
            'substitute_teacher_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'teacher')->where('is_active', true)),
            ],
            'makeup_for_schedule_id' => ['nullable', 'integer', 'exists:classroom_schedules,id'],
            'rescheduled_from_id' => ['nullable', 'integer', 'exists:classroom_schedules,id'],
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
            $overlap = ClassroomSchedule::query()
                ->where('classroom_id', $classroom->id)
                ->whereDate('session_date', $this->string('session_date'))
                ->whereNotIn('status', [ClassroomSchedule::STATUS_CANCELLED, ClassroomSchedule::STATUS_RESCHEDULED])
                ->where('start_time', '<', $this->string('end_time').':00')
                ->where('end_time', '>', $this->string('start_time').':00')
                ->when($schedule instanceof ClassroomSchedule, fn ($query) => $query->whereKeyNot($schedule->id))
                ->exists();

            if ($overlap && $this->isActiveSchedule()) {
                $validator->errors()->add('start_time', __('teacher-classroom::app.schedule_classroom_conflict'));
            }

            if ($this->isActiveSchedule() && $this->hasTeacherConflict($classroom, $schedule)) {
                $validator->errors()->add('start_time', __('teacher-classroom::app.schedule_teacher_conflict'));
            }

            $this->validateRelatedResources($validator, $classroom, $schedule);

            if ($validator->errors()->isNotEmpty()) {
                return;
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

            if ($course->ends_at && $sessionDate->startOfDay()->gt($course->ends_at->copy()->endOfDay())) {
                $validator->errors()->add('session_date', __('teacher-classroom::app.session_after_course_end', ['date' => $course->ends_at->format('d/m/Y')]));
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

    private function isActiveSchedule(): bool
    {
        return ! in_array($this->string('status')->toString(), [
            ClassroomSchedule::STATUS_DRAFT,
            ClassroomSchedule::STATUS_CANCELLED,
            ClassroomSchedule::STATUS_RESCHEDULED,
        ], true);
    }

    private function hasTeacherConflict(Classroom $classroom, mixed $currentSchedule): bool
    {
        $teacherIds = array_values(array_unique(array_filter([
            $classroom->teacher_id,
            $this->integer('substitute_teacher_id') ?: null,
        ])));

        if ($teacherIds === []) {
            return false;
        }

        return ClassroomSchedule::query()
            ->whereDate('session_date', $this->string('session_date'))
            ->whereNotIn('status', [ClassroomSchedule::STATUS_CANCELLED, ClassroomSchedule::STATUS_RESCHEDULED])
            ->where('start_time', '<', $this->string('end_time').':00')
            ->where('end_time', '>', $this->string('start_time').':00')
            ->where(function ($query) use ($teacherIds): void {
                $query->whereIn('substitute_teacher_id', $teacherIds)
                    ->orWhereHas('classroom', fn ($classrooms) => $classrooms->whereIn('teacher_id', $teacherIds));
            })
            ->when($currentSchedule instanceof ClassroomSchedule, fn ($query) => $query->whereKeyNot($currentSchedule->id))
            ->exists();
    }

    private function validateRelatedResources(Validator $validator, Classroom $classroom, mixed $currentSchedule): void
    {
        if ($this->filled('lesson_id')) {
            $validLesson = $classroom->course_id && Lesson::query()
                ->whereKey($this->integer('lesson_id'))
                ->whereHas('chapter', fn ($chapter) => $chapter->where('course_id', $classroom->course_id))
                ->exists();

            if (! $validLesson) {
                $validator->errors()->add('lesson_id', __('teacher-classroom::app.schedule_lesson_invalid'));
            }
        }

        if ($this->filled('substitute_teacher_id') && $this->integer('substitute_teacher_id') === (int) $classroom->teacher_id) {
            $validator->errors()->add('substitute_teacher_id', __('teacher-classroom::app.schedule_substitute_is_owner'));
        }

        foreach (['makeup_for_schedule_id', 'rescheduled_from_id'] as $field) {
            if (! $this->filled($field)) {
                continue;
            }

            $related = ClassroomSchedule::query()->find($this->integer($field));
            if (! $related || $related->classroom_id !== $classroom->id || ($currentSchedule instanceof ClassroomSchedule && $related->is($currentSchedule))) {
                $validator->errors()->add($field, __('teacher-classroom::app.schedule_related_session_invalid'));
            }
        }

        if ($this->filled('makeup_for_schedule_id') && $this->string('type')->toString() !== ClassroomSchedule::TYPE_MAKEUP) {
            $validator->errors()->add('makeup_for_schedule_id', __('teacher-classroom::app.schedule_makeup_reference_requires_makeup'));
        }
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
