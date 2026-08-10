<?php

namespace Mindigo\TeacherClassroom\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomAttendance;
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

    public function saveAttendance(Classroom $classroom, string $date, array $records): void
    {
        foreach ($records as $studentId => $record) {
            ClassroomAttendance::query()->updateOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'student_id' => (int) $studentId,
                    'session_date' => $date,
                ],
                [
                    'status' => $record['status'] ?? 'present',
                    'method' => 'manual',
                    'remarks' => $record['remarks'] ?? null,
                ]
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

    public function getAttendanceHistory(Classroom $classroom)
    {
        return ClassroomAttendance::query()
            ->where('classroom_id', $classroom->id)
            ->with('student:id,name,email')
            ->orderBy('session_date', 'desc')
            ->orderBy('student_id')
            ->get();
    }

    public function attendanceSession(Classroom $classroom, string $date): ?ClassroomAttendanceSession
    {
        return $classroom->attendanceSessions()->whereDate('session_date', $date)->first();
    }

    public function openCodeAttendance(Classroom $classroom, User $teacher, string $date, int $durationMinutes): ClassroomAttendanceSession
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
            $session = ClassroomAttendanceSession::query()
                ->where('classroom_id', $classroom->id)
                ->where('status', ClassroomAttendanceSession::STATUS_OPEN)
                ->where('expires_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $session || ! hash_equals($session->code, Str::upper(trim($code)))) {
                throw ValidationException::withMessages(['attendance_code' => __('student-classroom::app.attendance_code_invalid')]);
            }

            return ClassroomAttendance::query()->updateOrCreate(
                ['classroom_id' => $classroom->id, 'student_id' => $student->id, 'session_date' => $session->session_date],
                ['attendance_session_id' => $session->id, 'status' => 'present', 'method' => 'code', 'remarks' => null],
            );
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
            'substitute_teacher_id' => $data['substitute_teacher_id'] ?? null,
            'makeup_for_schedule_id' => $data['makeup_for_schedule_id'] ?? null,
            'rescheduled_from_id' => $data['rescheduled_from_id'] ?? null,
            'published_at' => ($data['status'] ?? ClassroomSchedule::STATUS_SCHEDULED) === ClassroomSchedule::STATUS_DRAFT ? null : now(),
            'created_by' => $actor?->id,
            'updated_by' => $actor?->id,
        ]);
    }

    public function updateSchedule(ClassroomSchedule $schedule, array $data, ?User $actor = null): ClassroomSchedule
    {
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
            'substitute_teacher_id' => $data['substitute_teacher_id'] ?? null,
            'makeup_for_schedule_id' => $data['makeup_for_schedule_id'] ?? null,
            'rescheduled_from_id' => $data['rescheduled_from_id'] ?? null,
            'published_at' => ($data['status'] ?? ClassroomSchedule::STATUS_SCHEDULED) === ClassroomSchedule::STATUS_DRAFT ? null : ($schedule->published_at ?? now()),
            'updated_by' => $actor?->id,
        ]);

        return $schedule;
    }

    public function deleteSchedule(ClassroomSchedule $schedule): void
    {
        $schedule->delete();
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
