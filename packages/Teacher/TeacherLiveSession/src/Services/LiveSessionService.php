<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Str;
use Mindigo\TeacherLiveSession\Models\LiveSession;

class LiveSessionService
{
    // Danh sách buổi học của GV

    public function getSessionsByTeacher(int|string $teacherId, ?int $classroomId = null, int $perPage = 10)
    {
        return LiveSession::query()
            ->byTeacher($teacherId)
            ->when($classroomId, fn ($q) => $q->where('classroom_id', $classroomId))
            ->with('classroom')
            ->latest('scheduled_start')
            ->paginate($perPage);
    }

    // Tạo buổi học — sinh room_name không trùng, không đoán được

    public function create(array $data): LiveSession
    {
        $data['teacher_id'] = auth()->id();
        $data['room_name']  = $this->generateRoomName();
        $data['status']     = 'scheduled';

        return LiveSession::create($data);
    }

    // Cập nhật buổi học

    public function update(LiveSession $session, array $data): LiveSession
    {
        $session->update($data);

        return $session->fresh();
    }

    // Xoá buổi học

    public function delete(LiveSession $session): void
    {
        $session->delete();
    }

    // Bắt đầu buổi học

    public function start(LiveSession $session): LiveSession
    {
        if ($session->status === 'scheduled') {
            $session->update([
                'status'     => 'live',
                'started_at' => now(),
            ]);
        }

        return $session->fresh();
    }

    // Kết thúc buổi học

    public function end(LiveSession $session): LiveSession
    {
        $session->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        return $session->fresh();
    }

    // Ghi nhận người tham gia (điểm danh)

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

    private function generateRoomName(): string
    {
        do {
            $name = 'mindigo-' . Str::lower(Str::random(16));
        } while (LiveSession::where('room_name', $name)->exists());

        return $name;
    }
}
