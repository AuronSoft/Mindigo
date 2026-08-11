<?php

namespace Mindigo\StudentLiveSession\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;

class LiveSessionService
{
    public function __construct(private readonly LiveMeetingProviderRegistry $providers) {}

    // ID các lớp mà học sinh đang tham gia (status active)

    public function classroomIdsForStudent(int|string $studentId): Collection
    {
        return Classroom::query()
            ->whereHas('students', function ($q) use ($studentId) {
                $q->where('student_id', $studentId)
                    ->where('classroom_students.status', 'active');
            })
            ->pluck('id');
    }

    // Các lớp của học sinh (dùng cho bộ lọc)

    public function getClassroomsForStudent(int|string $studentId)
    {
        return Classroom::query()
            ->whereIn('id', $this->classroomIdsForStudent($studentId))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    // Danh sách buổi học trực tuyến thuộc các lớp của học sinh

    public function getSessionsForStudent(int|string $studentId, ?int $classroomId = null, int $perPage = 10)
    {
        $classroomIds = $this->classroomIdsForStudent($studentId);

        return LiveSession::query()
            ->whereIn('classroom_id', $classroomIds)
            ->when($classroomId, fn ($q) => $q->where('classroom_id', $classroomId))
            ->where('status', '!=', 'cancelled')
            ->with(['classroom', 'teacher'])
            ->orderByRaw("FIELD(status, 'live', 'scheduled', 'ended')")
            ->orderByDesc('scheduled_start')
            ->paginate($perPage)
            ->withQueryString();
    }

    // Học sinh có quyền vào buổi học này không (phải thuộc lớp của buổi học)

    public function canAccess(LiveSession $session, int|string $studentId): bool
    {
        return $this->classroomIdsForStudent($studentId)->contains($session->classroom_id);
    }

    // Ghi nhận học sinh tham gia (điểm danh)

    public function recordJoin(LiveSession $session, int|string $userId): void
    {
        $attendance = $session->attendances()->firstOrCreate(
            ['user_id' => $userId],
            ['joined_at' => now()],
        );

        if (! $attendance->joined_at) {
            $attendance->update(['joined_at' => now()]);
        }
    }

    public function join(LiveSession $session, User $user): array
    {
        $result = $this->providers->resolve($session->provider)->join($session, $user);
        $this->recordJoin($session, $user->getKey());

        return [
            'mode' => $result->mode,
            'url' => $result->url,
            'token' => $result->token,
            'metadata' => $result->metadata,
        ];
    }
}
