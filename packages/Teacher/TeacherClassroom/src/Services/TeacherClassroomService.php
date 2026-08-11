<?php

namespace Mindigo\TeacherClassroom\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\AcademicCalendar\Models\AcademicCalendarException;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomAttendance;
use Mindigo\TeacherClassroom\Models\ClassroomAttendanceRevision;
use Mindigo\TeacherClassroom\Models\ClassroomAttendanceSession;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseClassroomAssignment;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Services\CourseEnrollmentService;

class TeacherClassroomService
{
    public function __construct(private readonly CourseEnrollmentService $courseEnrollmentService) {}

    /**
     * Danh sách lớp học CHỈ của giáo viên đang đăng nhập.
     */
    public function ownedList(User $teacher, array $filters = []): LengthAwarePaginator
    {
        $query = Classroom::query()
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->with(['subject:id,name,color', 'course:id,name,subject_id'])
            ->withCount('students')
            ->latest('updated_at');

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('school_year', 'like', "%{$keyword}%");
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(12)->withQueryString();
    }

    public function stats(User $teacher): array
    {
        $row = DB::table('classrooms')
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("COUNT(CASE WHEN status = 'active' THEN 1 END) as active")
            ->selectRaw("COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive")
            ->selectRaw('COALESCE(SUM((
                SELECT COUNT(*) FROM classroom_students
                WHERE classroom_students.classroom_id = classrooms.id
                  AND classroom_students.status = ?
            )), 0) as students', ['active'])
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'active' => (int) ($row->active ?? 0),
            'inactive' => (int) ($row->inactive ?? 0),
            'students' => (int) ($row->students ?? 0),
        ];
    }

    public function create(User $teacher, array $data): Classroom
    {
        return DB::transaction(function () use ($teacher, $data): Classroom {
            $data = $this->resolveAcademicContext($teacher, $data);
            $classroom = Classroom::query()->create([
                ...$data,
                'created_by' => $teacher->getAuthIdentifier(),
                'teacher_id' => $teacher->getAuthIdentifier(),
                'code' => Str::upper($data['code']),
                'slug' => $this->uniqueSlug($data['name']),
            ]);

            $this->syncAcademicLinks($classroom, null);

            return $classroom;
        });
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        return DB::transaction(function () use ($classroom, $data): Classroom {
            $previousCourseId = $classroom->course_id;
            $data = $this->resolveAcademicContext($classroom->teacher, $data);
            $classroom->fill([
                ...$data,
                'code' => Str::upper($data['code']),
                'slug' => $classroom->name === $data['name'] ? $classroom->slug : $this->uniqueSlug($data['name'], $classroom),
            ])->save();

            $this->syncAcademicLinks($classroom, $previousCourseId);

            return $classroom;
        });
    }

    public function syncStudents(Classroom $classroom, array $studentIds): void
    {
        $removedStudentIds = $classroom->students()
            ->where('classroom_students.status', 'active')
            ->whereNotIn('users.id', array_map('intval', $studentIds))
            ->pluck('users.id');

        $payload = collect($studentIds)
            ->mapWithKeys(fn ($id) => [(int) $id => ['status' => 'active', 'joined_at' => now()]])
            ->all();

        $classroom->students()->sync($payload);

        if ($classroom->type === Classroom::TYPE_COURSE && $classroom->course_id) {
            if ($removedStudentIds->isNotEmpty()) {
                CourseEnrollment::query()
                    ->where('course_id', $classroom->course_id)
                    ->where('classroom_id', $classroom->id)
                    ->whereIn('student_id', $removedStudentIds)
                    ->whereIn('status', [
                        CourseEnrollment::STATUS_INVITED,
                        CourseEnrollment::STATUS_ENROLLED,
                        CourseEnrollment::STATUS_IN_PROGRESS,
                    ])
                    ->update([
                        'status' => CourseEnrollment::STATUS_WITHDRAWN,
                        'classroom_id' => null,
                        'distribution_id' => null,
                        'withdrawn_at' => now(),
                    ]);
            }

            $this->courseEnrollmentService->assignToClassrooms($classroom->course()->firstOrFail(), $classroom->teacher()->firstOrFail(), [
                'classroom_ids' => [$classroom->id],
                'starts_at' => null,
                'due_at' => null,
                'is_mandatory' => true,
                'visibility' => 'visible',
            ]);
        }
    }

    /**
     * Dữ liệu cho form (danh sách học sinh để gán vào lớp).
     */
    public function formData(): array
    {
        return [
            'statuses' => Classroom::STATUSES,
            'students' => User::query()->students()->active()->orderBy('name')->get(['id', 'name', 'email']),
            'teachers' => User::query()->where('role', 'teacher')->active()->whereKeyNot(auth()->id())->orderBy('name')->get(['id', 'name', 'email']),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'color']),
            'schoolYears' => Classroom::schoolYearOptions(),
            'courses' => Course::query()
                ->where('teacher_id', auth()->id())
                ->whereNotNull('subject_id')
                ->where('publication_status', Course::PUBLICATION_PUBLISHED)
                ->where('is_active', true)
                ->with('subject:id,name,color')
                ->orderBy('name')
                ->get(['id', 'name', 'subject_id', 'publication_status']),
        ];
    }

    public function saveAttendance(Classroom $classroom, string $date, array $records, ?User $actor = null, ?string $changeReason = null): void
    {
        $actor ??= $classroom->teacher()->firstOrFail();
        foreach ($records as $studentId => $record) {
            $this->persistAttendance(
                [
                    'classroom_id' => $classroom->id,
                    'student_id' => (int) $studentId,
                    'session_date' => $date,
                ],
                $record,
                $actor,
                $changeReason,
            );
        }
    }

    public function getAttendanceByDate(Classroom $classroom, string $date)
    {
        return ClassroomAttendance::query()
            ->where('classroom_id', $classroom->id)
            ->whereDate('session_date', $date)
            ->get()
            ->keyBy('student_id');
    }

    public function getAttendanceBySchedule(ClassroomSchedule $schedule)
    {
        return ClassroomAttendance::query()->where('classroom_schedule_id', $schedule->id)->get()->keyBy('student_id');
    }

    public function saveScheduleAttendance(ClassroomSchedule $schedule, array $records, ?User $actor = null, ?string $changeReason = null): void
    {
        $actor ??= $schedule->classroom()->firstOrFail()->teacher()->firstOrFail();
        foreach ($records as $studentId => $record) {
            $this->persistAttendance(
                ['classroom_schedule_id' => $schedule->id, 'student_id' => $studentId],
                ['classroom_id' => $schedule->classroom_id, 'session_date' => $schedule->session_date, ...$record],
                $actor,
                $changeReason,
            );
        }
    }

    private function persistAttendance(array $identity, array $record, User $actor, ?string $changeReason): ClassroomAttendance
    {
        return DB::transaction(function () use ($identity, $record, $actor, $changeReason): ClassroomAttendance {
            $attendanceQuery = ClassroomAttendance::query();
            foreach ($identity as $column => $value) {
                $column === 'session_date'
                    ? $attendanceQuery->whereDate($column, $value)
                    : $attendanceQuery->where($column, $value);
            }
            $attendance = $attendanceQuery->lockForUpdate()->first();
            $oldValues = $attendance?->only(['status', 'late_minutes', 'absence_reason', 'remarks', 'method']) ?? [];
            $values = [
                'status' => $record['status'] ?? 'present',
                'late_minutes' => ($record['status'] ?? null) === 'late' ? ($record['late_minutes'] ?? null) : null,
                'absence_reason' => ($record['status'] ?? null) === 'excused' ? ($record['absence_reason'] ?? null) : null,
                'method' => 'manual',
                'remarks' => $record['remarks'] ?? null,
                'updated_by' => $actor->id,
            ];
            $attendance ??= new ClassroomAttendance($identity);
            $attendance->fill([...$identity, ...$values])->save();
            $newValues = $attendance->only(['status', 'late_minutes', 'absence_reason', 'remarks', 'method']);

            if ($oldValues === [] || $oldValues !== $newValues) {
                ClassroomAttendanceRevision::query()->create([
                    'attendance_id' => $attendance->id,
                    'changed_by' => $actor->id,
                    'old_values' => $oldValues ?: null,
                    'new_values' => $newValues,
                    'change_reason' => $changeReason,
                ]);
            }

            return $attendance;
        });
    }

    public function getAttendanceHistory(Classroom $classroom)
    {
        return ClassroomAttendance::query()
            ->where('classroom_id', $classroom->id)
            ->with(['student:id,name,email', 'editor:id,name', 'revisions.editor:id,name'])
            ->orderBy('session_date', 'desc')
            ->orderBy('student_id')
            ->get();
    }

    public function attendanceSession(Classroom $classroom, string $date): ?ClassroomAttendanceSession
    {
        return $classroom->attendanceSessions()->whereDate('session_date', $date)->first();
    }

    public function attendanceSessionForSchedule(ClassroomSchedule $schedule): ?ClassroomAttendanceSession
    {
        return ClassroomAttendanceSession::query()->where('classroom_schedule_id', $schedule->id)->first();
    }

    public function openCodeAttendance(Classroom $classroom, User $teacher, string $date, int $durationMinutes, int $lateAfterMinutes = 15): ClassroomAttendanceSession
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = collect(range(1, 6))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->join('');

        return ClassroomAttendanceSession::query()->updateOrCreate(
            ['classroom_id' => $classroom->id, 'session_date' => $date],
            [
                'opened_by' => $teacher->id,
                'code' => $code,
                'status' => ClassroomAttendanceSession::STATUS_OPEN,
                'expires_at' => now()->addMinutes($durationMinutes),
                'late_after_minutes' => $lateAfterMinutes,
                'closed_at' => null,
            ],
        );
    }

    public function openScheduleAttendance(ClassroomSchedule $schedule, User $teacher, int $durationMinutes, int $lateAfterMinutes = 15): ClassroomAttendanceSession
    {
        $start = Carbon::parse($schedule->session_date->format('Y-m-d').' '.$schedule->start_time, config('app.timezone'));
        $end = Carbon::parse($schedule->session_date->format('Y-m-d').' '.$schedule->end_time, config('app.timezone'));
        if (now()->lt($start->copy()->subHour()) || now()->gt($end->copy()->addMinutes(30))) {
            throw ValidationException::withMessages(['attendance' => __('teacher-classroom::app.attendance_outside_session_window')]);
        }

        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = collect(range(1, 6))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->join('');

        return ClassroomAttendanceSession::query()->updateOrCreate(
            ['classroom_schedule_id' => $schedule->id],
            [
                'classroom_id' => $schedule->classroom_id, 'opened_by' => $teacher->id,
                'session_date' => $schedule->session_date, 'code' => $code,
                'status' => ClassroomAttendanceSession::STATUS_OPEN,
                'expires_at' => min(now()->addMinutes($durationMinutes), $end->copy()->addMinutes(30)),
                'late_after_minutes' => $lateAfterMinutes,
                'closed_at' => null,
            ],
        );
    }

    public function closeCodeAttendance(ClassroomAttendanceSession $session): void
    {
        $session->update(['status' => ClassroomAttendanceSession::STATUS_CLOSED, 'closed_at' => now()]);
    }

    public function checkInWithCode(Classroom $classroom, User $student, string $code): ClassroomAttendance
    {
        return DB::transaction(function () use ($classroom, $student, $code): ClassroomAttendance {
            $sessions = ClassroomAttendanceSession::query()
                ->where('classroom_id', $classroom->id)
                ->where('status', ClassroomAttendanceSession::STATUS_OPEN)
                ->where('expires_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->get();

            $normalizedCode = Str::upper(trim($code));
            $session = $sessions->first(fn (ClassroomAttendanceSession $candidate): bool => hash_equals($candidate->code, $normalizedCode));

            if (! $session) {
                throw ValidationException::withMessages(['attendance_code' => __('student-classroom::app.attendance_code_invalid')]);
            }

            $status = 'present';
            $lateMinutes = null;
            if ($session->schedule) {
                $start = Carbon::parse($session->schedule->session_date->format('Y-m-d').' '.$session->schedule->start_time, config('app.timezone'));
                if (now()->gt($start->copy()->addMinutes($session->late_after_minutes))) {
                    $status = 'late';
                    $lateMinutes = max(1, (int) $start->diffInMinutes(now()));
                }
            }

            $attendance = ClassroomAttendance::query()->updateOrCreate(
                $session->classroom_schedule_id
                    ? ['classroom_schedule_id' => $session->classroom_schedule_id, 'student_id' => $student->id]
                    : ['classroom_id' => $classroom->id, 'student_id' => $student->id, 'session_date' => $session->session_date],
                ['classroom_id' => $classroom->id, 'classroom_schedule_id' => $session->classroom_schedule_id, 'session_date' => $session->session_date, 'attendance_session_id' => $session->id, 'status' => $status, 'late_minutes' => $lateMinutes, 'absence_reason' => null, 'method' => 'code', 'remarks' => null, 'updated_by' => $student->id],
            );

            ClassroomAttendanceRevision::query()->firstOrCreate(
                ['attendance_id' => $attendance->id],
                ['changed_by' => $student->id, 'new_values' => $attendance->only(['status', 'late_minutes', 'absence_reason', 'remarks', 'method'])],
            );

            return $attendance;
        });
    }

    public function addSchedule(Classroom $classroom, array $data, ?User $actor = null): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id,
            'lesson_id' => $data['lesson_id'] ?? null,
            'type' => $data['type'],
            'delivery_mode' => $data['delivery_mode'] ?? ClassroomSchedule::DELIVERY_OFFLINE,
            'status' => $data['status'] ?? ClassroomSchedule::STATUS_SCHEDULED,
            'title' => $data['title'],
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? null,
            'description' => $data['description'] ?? null,
            'makeup_reason' => $data['makeup_reason'] ?? null,
            'cancel_reason' => $data['cancel_reason'] ?? null,
            'reschedule_reason' => $data['reschedule_reason'] ?? null,
            'substitute_teacher_id' => $data['substitute_teacher_id'] ?? null,
            'substitute_status' => ! empty($data['substitute_teacher_id']) ? ClassroomSchedule::SUBSTITUTE_PENDING : null,
            'makeup_for_schedule_id' => $data['makeup_for_schedule_id'] ?? null,
            'rescheduled_from_id' => $data['rescheduled_from_id'] ?? null,
            'published_at' => ($data['status'] ?? ClassroomSchedule::STATUS_SCHEDULED) === ClassroomSchedule::STATUS_DRAFT ? null : now(),
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);
    }

    public function generateCourseSchedulePlan(Classroom $classroom, User $actor, string $startDate, int $sessionCount): array
    {
        if ($classroom->type !== Classroom::TYPE_COURSE || ! $classroom->course_id) {
            throw ValidationException::withMessages(['session_count' => __('teacher-classroom::app.course_plan_course_only')]);
        }

        $course = $classroom->course()->with('chapters.lessons')->firstOrFail();
        if (! $course->starts_at || empty($course->schedule_days) || ! preg_match('/^(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})$/', (string) $course->study_time, $times)) {
            throw ValidationException::withMessages(['session_count' => __('teacher-classroom::app.course_schedule_incomplete')]);
        }

        $dayCodes = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $allowedDays = collect($course->schedule_days)->map(fn (string $day) => $dayCodes[$day] ?? null)->filter(fn ($day) => $day !== null)->all();
        $lessons = $course->chapters->sortBy('sort_order')->flatMap(fn ($chapter) => $chapter->lessons->sortBy('sort_order'))->values();
        $exceptionDates = AcademicCalendarException::query()
            ->where('kind', AcademicCalendarException::KIND_NO_CLASS)
            ->where(function ($query) use ($classroom, $course): void {
                $query->where(fn ($global) => $global->whereNull('course_id')->whereNull('classroom_id'))
                    ->orWhere('course_id', $course->id)
                    ->orWhere('classroom_id', $classroom->id);
            })
            ->pluck('exception_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        return DB::transaction(function () use ($classroom, $actor, $course, $startDate, $sessionCount, $allowedDays, $lessons, $times, $exceptionDates): array {
            Classroom::query()->whereKey($classroom->id)->lockForUpdate()->first();
            $cursor = Carbon::parse($startDate)->startOfDay();
            $planned = $created = $skipped = $exceptions = 0;
            $scanLimit = 730;

            while ($planned < $sessionCount && $scanLimit-- > 0) {
                if ($course->ends_at && $cursor->gt($course->ends_at->copy()->endOfDay())) {
                    break;
                }

                if (! in_array($cursor->dayOfWeek, $allowedDays, true)) {
                    $cursor->addDay();

                    continue;
                }

                if ($exceptionDates->has($cursor->toDateString())) {
                    $exceptions++;
                    $cursor->addDay();

                    continue;
                }

                $lesson = $lessons->get($planned);
                $exists = ClassroomSchedule::query()
                    ->where('classroom_id', $classroom->id)
                    ->whereDate('session_date', $cursor->toDateString())
                    ->whereIn('start_time', [$times[1], $times[1].':00'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    $this->addSchedule($classroom, [
                        'type' => ClassroomSchedule::TYPE_REGULAR,
                        'lesson_id' => $lesson?->id,
                        'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE,
                        'status' => ClassroomSchedule::STATUS_SCHEDULED,
                        'title' => $lesson?->name ?: __('teacher-classroom::app.generated_session_title', ['course' => $course->name, 'number' => $planned + 1]),
                        'session_date' => $cursor->toDateString(),
                        'start_time' => $times[1],
                        'end_time' => $times[2],
                    ], $actor);
                    $created++;
                }

                $planned++;
                $cursor->addDay();
            }

            if ($planned < $sessionCount) {
                throw ValidationException::withMessages(['session_count' => __('teacher-classroom::app.course_plan_range_too_large')]);
            }

            return ['created' => $created, 'skipped' => $skipped, 'exceptions' => $exceptions, 'planned' => $planned];
        });
    }

    public function storeCalendarException(Classroom $classroom, User $actor, array $data): AcademicCalendarException
    {
        return AcademicCalendarException::query()->updateOrCreate(
            ['classroom_id' => $classroom->id, 'exception_date' => $data['exception_date']],
            ['course_id' => $classroom->course_id, 'created_by' => $actor->id, 'kind' => AcademicCalendarException::KIND_NO_CLASS, 'title' => $data['title'], 'reason' => $data['reason']],
        );
    }

    public function updateSchedule(ClassroomSchedule $schedule, array $data, ?User $actor = null): ClassroomSchedule
    {
        $substituteId = $data['substitute_teacher_id'] ?? null;
        $substituteChanged = (int) $schedule->substitute_teacher_id !== (int) $substituteId;
        $schedule->update([
            'lesson_id' => $data['lesson_id'] ?? null,
            'type' => $data['type'],
            'delivery_mode' => $data['delivery_mode'] ?? ClassroomSchedule::DELIVERY_OFFLINE,
            'status' => $data['status'] ?? ClassroomSchedule::STATUS_SCHEDULED,
            'title' => $data['title'],
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'location' => $data['location'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? null,
            'description' => $data['description'] ?? null,
            'makeup_reason' => $data['makeup_reason'] ?? null,
            'cancel_reason' => $data['cancel_reason'] ?? null,
            'substitute_teacher_id' => $substituteId,
            'substitute_status' => $substituteChanged ? ($substituteId ? ClassroomSchedule::SUBSTITUTE_PENDING : null) : $schedule->substitute_status,
            'substitute_responded_at' => $substituteChanged ? null : $schedule->substitute_responded_at,
            'substitute_response_note' => $substituteChanged ? null : $schedule->substitute_response_note,
            'makeup_for_schedule_id' => $data['makeup_for_schedule_id'] ?? null,
            'rescheduled_from_id' => $data['rescheduled_from_id'] ?? null,
            'published_at' => ($data['status'] ?? ClassroomSchedule::STATUS_SCHEDULED) === ClassroomSchedule::STATUS_DRAFT ? null : ($schedule->published_at ?? now()),
            'updated_by' => $actor?->id,
        ]);

        return $schedule;
    }

    public function respondToSubstituteAssignment(ClassroomSchedule $schedule, User $teacher, bool $accept, ?string $note): ClassroomSchedule
    {
        abort_unless($schedule->substitute_teacher_id === (int) $teacher->getAuthIdentifier(), 403);
        abort_unless($schedule->substitute_status === ClassroomSchedule::SUBSTITUTE_PENDING, 422);

        $schedule->update([
            'substitute_status' => $accept ? ClassroomSchedule::SUBSTITUTE_ACCEPTED : ClassroomSchedule::SUBSTITUTE_DECLINED,
            'substitute_responded_at' => now(),
            'substitute_response_note' => $note,
            'updated_by' => $teacher->id,
        ]);

        return $schedule->refresh();
    }

    public function deleteSchedule(ClassroomSchedule $schedule): void
    {
        $schedule->delete();
    }

    public function cancelSchedule(ClassroomSchedule $schedule, string $reason, User $actor): ClassroomSchedule
    {
        $schedule->update([
            'status' => ClassroomSchedule::STATUS_CANCELLED,
            'cancel_reason' => $reason,
            'updated_by' => $actor->id,
        ]);

        return $schedule->refresh();
    }

    public function updateScheduleDetails(ClassroomSchedule $schedule, array $data, User $actor): ClassroomSchedule
    {
        $schedule->update([
            'title' => $data['title'],
            'delivery_mode' => $data['delivery_mode'],
            'location' => $data['location'] ?? null,
            'meeting_url' => $data['meeting_url'] ?? null,
            'description' => $data['description'] ?? null,
            'updated_by' => $actor->id,
        ]);

        return $schedule->refresh();
    }

    public function rescheduleSchedule(ClassroomSchedule $schedule, array $data, User $actor): ClassroomSchedule
    {
        return DB::transaction(function () use ($schedule, $data, $actor): ClassroomSchedule {
            $replacement = $this->addSchedule($schedule->classroom, [
                ...$data,
                'rescheduled_from_id' => $schedule->id,
                'status' => ClassroomSchedule::STATUS_SCHEDULED,
            ], $actor);

            $schedule->update([
                'status' => ClassroomSchedule::STATUS_RESCHEDULED,
                'reschedule_reason' => $data['reschedule_reason'],
                'updated_by' => $actor->id,
            ]);

            return $replacement;
        });
    }

    public function completeSchedule(ClassroomSchedule $schedule, User $actor): ClassroomSchedule
    {
        $schedule->update([
            'status' => ClassroomSchedule::STATUS_COMPLETED,
            'updated_by' => $actor->id,
        ]);

        return $schedule->refresh();
    }

    private function uniqueSlug(string $name, ?Classroom $ignore = null): string
    {
        $base = Str::slug($name) ?: 'lop-hoc';
        $slug = $base;
        $i = 1;

        while (
            Classroom::query()
                ->where('slug', $slug)
                ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->getKey()))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    private function resolveAcademicContext(User $teacher, array $data): array
    {
        if ($data['type'] === Classroom::TYPE_COURSE) {
            $course = Course::query()
                ->where('teacher_id', $teacher->getAuthIdentifier())
                ->whereNotNull('subject_id')
                ->where('publication_status', Course::PUBLICATION_PUBLISHED)
                ->where('is_active', true)
                ->findOrFail($data['course_id']);

            $data['subject_id'] = $course->subject_id;
        } else {
            $data['course_id'] = null;
        }

        return $data;
    }

    private function syncAcademicLinks(Classroom $classroom, ?int $previousCourseId): void
    {
        $classroom->subjects()->sync([$classroom->subject_id]);

        if ($previousCourseId && $previousCourseId !== $classroom->course_id) {
            CourseEnrollment::query()
                ->where('course_id', $previousCourseId)
                ->where('classroom_id', $classroom->id)
                ->whereIn('status', [CourseEnrollment::STATUS_INVITED, CourseEnrollment::STATUS_ENROLLED])
                ->update([
                    'status' => CourseEnrollment::STATUS_WITHDRAWN,
                    'classroom_id' => null,
                    'distribution_id' => null,
                    'withdrawn_at' => now(),
                ]);

            CourseClassroomAssignment::query()
                ->where('course_id', $previousCourseId)
                ->where('classroom_id', $classroom->id)
                ->delete();

            Course::query()->whereKey($previousCourseId)->update([
                'enrollment_count' => CourseEnrollment::query()
                    ->where('course_id', $previousCourseId)
                    ->whereIn('status', CourseEnrollment::ACTIVE_STATUSES)
                    ->count(),
            ]);
        }

        if ($classroom->course_id) {
            $this->courseEnrollmentService->assignToClassrooms($classroom->course()->firstOrFail(), $classroom->teacher()->firstOrFail(), [
                'classroom_ids' => [$classroom->id],
                'starts_at' => null,
                'due_at' => null,
                'is_mandatory' => true,
                'visibility' => 'visible',
            ]);
        }
    }
}
