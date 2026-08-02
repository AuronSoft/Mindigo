<?php

namespace Mindigo\TeacherClassroom\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\ClassroomAttendance;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;

class TeacherClassroomService
{
    /**
     * Danh sách lớp học CHỈ của giáo viên đang đăng nhập.
     */
    public function ownedList(User $teacher, array $filters = []): LengthAwarePaginator
    {
        $query = Classroom::query()
            ->where('teacher_id', $teacher->getAuthIdentifier())
            ->with(['subjects:id,name,color'])
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
        $base = Classroom::query()->where('teacher_id', $teacher->getAuthIdentifier());

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'students' => (clone $base)->withCount('students')->get()->sum('students_count'),
        ];
    }

    public function create(User $teacher, array $data): Classroom
    {
        return Classroom::query()->create([
            ...$data,
            'created_by' => $teacher->getAuthIdentifier(),
            'teacher_id' => $teacher->getAuthIdentifier(),
            'code' => Str::upper($data['code']),
            'slug' => $this->uniqueSlug($data['name']),
        ]);
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        $classroom->fill([
            ...$data,
            'code' => Str::upper($data['code']),
            'slug' => $classroom->name === $data['name'] ? $classroom->slug : $this->uniqueSlug($data['name'], $classroom),
        ])->save();

        return $classroom;
    }

    public function syncStudents(Classroom $classroom, array $studentIds): void
    {
        $payload = collect($studentIds)
            ->mapWithKeys(fn ($id) => [(int) $id => ['status' => 'active', 'joined_at' => now()]])
            ->all();

        $classroom->students()->sync($payload);
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
            'assistants' => User::query()->teachers()->active()->orderBy('name')->get(['id', 'name', 'email']),
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
                    'remarks' => $record['remarks'] ?? null,
                ]
            );
        }
    }

    public function getAttendanceByDate(Classroom $classroom, string $date)
    {
        return ClassroomAttendance::query()
            ->where('classroom_id', $classroom->id)
            ->where('session_date', $date)
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

    public function addSchedule(Classroom $classroom, array $data): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id,
            'title' => $data['title'],
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function updateSchedule(ClassroomSchedule $schedule, array $data): ClassroomSchedule
    {
        $schedule->update([
            'title' => $data['title'],
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'description' => $data['description'] ?? null,
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
}
