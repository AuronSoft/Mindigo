<?php

namespace Mindigo\TeacherLiveSession\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LiveSessionReportService
{
    public function report(User $actor, array $filters): array
    {
        $scope = $filters['scope'] ?? 'session';
        $sessions = $this->query($actor, $filters)->get();
        $facts = $sessions->flatMap(fn (LiveSession $session) => $this->facts($session));
        if ($scope === 'student' && filled($filters['entity_id'] ?? null)) {
            $facts = $facts->where('student_id', (int) $filters['entity_id'])->values();
        }

        return [
            'scope' => $scope,
            'rows' => $this->aggregate($facts, $scope),
            'summary' => $this->summary($facts, $sessions),
            'sessions' => $sessions,
        ];
    }

    public function export(User $actor, array $filters): Response|StreamedResponse
    {
        $report = $this->report($actor, $filters);
        $format = $filters['format'] ?? 'csv';
        $headers = $this->headers();
        $rows = $report['rows']->map(fn (array $row) => $this->exportRow($row));
        $filename = 'mindigo-live-'.$report['scope'].'-report-'.now()->format('Ymd-His');

        if ($format === 'pdf') {
            return Pdf::loadView('teacher-live-session::reports.pdf', compact('report', 'headers', 'rows'))
                ->download($filename.'.pdf');
        }

        $delimiter = $format === 'xlsx' ? "\t" : ',';
        $extension = $format === 'xlsx' ? 'xls' : 'csv';
        $mime = $format === 'xlsx' ? 'application/vnd.ms-excel' : 'text/csv';

        return response()->streamDownload(function () use ($headers, $rows, $delimiter): void {
            $stream = fopen('php://output', 'wb');
            if ($stream === false) {
                return;
            }
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers, $delimiter, '"', '');
            foreach ($rows as $row) {
                fputcsv($stream, $row, $delimiter, '"', '');
            }
            fclose($stream);
        }, $filename.'.'.$extension, ['Content-Type' => $mime.'; charset=UTF-8']);
    }

    private function query(User $actor, array $filters): Builder
    {
        return LiveSession::query()
            ->when($actor->role === 'teacher', fn (Builder $query) => $query->where('teacher_id', $actor->id))
            ->when($filters['provider'] ?? null, fn (Builder $query, string $provider) => $query->where('provider', $provider))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('scheduled_start', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('scheduled_start', '<=', $date))
            ->when(($filters['scope'] ?? null) === 'session' && ($filters['entity_id'] ?? null), fn (Builder $query) => $query->whereKey($filters['entity_id']))
            ->when(($filters['scope'] ?? null) === 'classroom' && ($filters['entity_id'] ?? null), fn (Builder $query) => $query->where('classroom_id', $filters['entity_id']))
            ->when(($filters['scope'] ?? null) === 'course' && ($filters['entity_id'] ?? null), fn (Builder $query) => $query->whereHas('classroom', fn (Builder $classrooms) => $classrooms->where('course_id', $filters['entity_id'])))
            ->when(($filters['scope'] ?? null) === 'teacher' && ($filters['entity_id'] ?? null), fn (Builder $query) => $query->where('teacher_id', $filters['entity_id']))
            ->with(['classroom.course:id,name', 'classroom.students' => fn ($query) => $query->wherePivot('status', 'active'), 'teacher:id,name', 'attendances.user:id,name,email', 'attendances.segments'])
            ->latest('scheduled_start')
            ->limit(10000);
    }

    private function facts(LiveSession $session): Collection
    {
        $attendances = $session->attendances->keyBy('user_id');
        $students = $session->classroom?->students ?? collect();
        if ($students->isEmpty()) {
            $students = $session->attendances->pluck('user')->filter();
        }

        return $students->map(function ($student) use ($session, $attendances): array {
            $attendance = $attendances->get($student->id);
            $earlyLeave = $attendance?->left_at && $session->scheduled_end && $attendance->left_at->lt($session->scheduled_end)
                ? (int) $attendance->left_at->diffInMinutes($session->scheduled_end)
                : 0;

            return [
                'session_id' => $session->id, 'session' => $session->title,
                'classroom_id' => $session->classroom_id, 'classroom' => $session->classroom?->name,
                'course_id' => $session->classroom?->course_id, 'course' => $session->classroom?->course?->name,
                'teacher_id' => $session->teacher_id, 'teacher' => $session->teacher?->name,
                'student_id' => $student->id, 'student' => $student->name,
                'provider' => $session->provider->value, 'status' => $attendance?->attendance_status ?? 'absent',
                'total_seconds' => (int) ($attendance?->total_seconds ?? 0), 'late_minutes' => (int) ($attendance?->late_minutes ?? 0),
                'early_leave_minutes' => $earlyLeave, 'reconnects' => max(0, (int) ($attendance?->join_count ?? 0) - 1),
                'chat' => (int) ($attendance?->chat_messages_count ?? 0), 'reactions' => (int) ($attendance?->reactions_count ?? 0),
                'hands' => (int) ($attendance?->hands_raised_count ?? 0), 'polls' => (int) ($attendance?->poll_votes_count ?? 0),
                'microphone_seconds' => (int) ($attendance?->microphone_seconds ?? 0),
                'camera_seconds' => (int) ($attendance?->camera_seconds ?? 0),
                'connection_errors' => $attendance?->segments->where('leave_reason', 'connection_lost')->count() ?? 0,
            ];
        });
    }

    private function aggregate(Collection $facts, string $scope): Collection
    {
        [$key, $label] = match ($scope) {
            'classroom' => ['classroom_id', 'classroom'], 'course' => ['course_id', 'course'],
            'teacher' => ['teacher_id', 'teacher'], 'student' => ['student_id', 'student'],
            'provider' => ['provider', 'provider'], default => ['session_id', 'session'],
        };

        return $facts->groupBy(fn (array $fact) => (string) ($fact[$key] ?? 'unassigned'))
            ->map(function (Collection $group) use ($key, $label): array {
                $first = $group->first();
                $participants = $group->unique('student_id')->count();
                $present = $group->whereIn('status', ['present', 'late', 'partial'])->count();

                return [
                    'id' => $first[$key] ?? null, 'label' => $first[$label] ?: __('teacher-live-session::app.report_unassigned'),
                    'sessions' => $group->unique('session_id')->count(), 'participants' => $participants,
                    'attendance_rate' => $group->count() ? round($present / $group->count() * 100, 1) : 0,
                    'total_minutes' => (int) round($group->sum('total_seconds') / 60),
                    'late' => $group->where('late_minutes', '>', 0)->count(),
                    'early_leave' => $group->where('early_leave_minutes', '>', 0)->count(),
                    'reconnects' => $group->sum('reconnects'), 'chat' => $group->sum('chat'),
                    'reactions' => $group->sum('reactions'), 'hands' => $group->sum('hands'),
                    'polls' => $group->sum('polls'), 'connection_errors' => $group->sum('connection_errors'),
                    'microphone_minutes' => (int) round($group->sum('microphone_seconds') / 60),
                    'camera_minutes' => (int) round($group->sum('camera_seconds') / 60),
                ];
            })->values();
    }

    private function summary(Collection $facts, Collection $sessions): array
    {
        $present = $facts->whereIn('status', ['present', 'late', 'partial'])->count();

        return ['sessions' => $sessions->count(), 'participants' => $facts->unique('student_id')->count(),
            'attendance_rate' => $facts->count() ? round($present / $facts->count() * 100, 1) : 0,
            'total_minutes' => (int) round($facts->sum('total_seconds') / 60),
            'connection_errors' => $facts->sum('connection_errors')];
    }

    private function headers(): array
    {
        return ['Phạm vi', 'Số buổi', 'Người tham gia', 'Chuyên cần (%)', 'Tổng phút', 'Đi muộn', 'Rời sớm', 'Reconnect', 'Phút mic', 'Phút camera', 'Chat', 'Reaction', 'Giơ tay', 'Poll', 'Lỗi kết nối'];
    }

    private function exportRow(array $row): array
    {
        return [$row['label'], $row['sessions'], $row['participants'], $row['attendance_rate'], $row['total_minutes'], $row['late'], $row['early_leave'], $row['reconnects'], $row['microphone_minutes'], $row['camera_minutes'], $row['chat'], $row['reactions'], $row['hands'], $row['polls'], $row['connection_errors']];
    }
}
