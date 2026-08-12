<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Mindigo\TeacherLiveSession\Services\LiveSessionAccessService;
use Mindigo\TeacherLiveSession\Services\LiveSessionAttendanceReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LiveSessionAttendanceReportController extends Controller
{
    public function __construct(
        private readonly LiveSessionAccessService $access,
        private readonly LiveSessionAttendanceReportService $reports,
    ) {}

    public function show(Request $request, LiveSession $liveSession): View
    {
        abort_unless($this->access->canModerate($liveSession, $request->user()), 403);
        $rows = $this->reports->rows($liveSession);

        return view('teacher-live-session::attendance-report', ['session' => $liveSession->load('classroom'), 'rows' => $rows, 'summary' => $this->reports->summary($rows)]);
    }

    public function export(Request $request, LiveSession $liveSession): StreamedResponse
    {
        abort_unless($this->access->canModerate($liveSession, $request->user()), 403);
        $rows = $this->reports->rows($liveSession);

        return response()->streamDownload(function () use ($rows): void {
            $stream = fopen('php://output', 'wb');
            if ($stream === false) {
                return;
            }
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Học viên', 'Email', 'Trạng thái', 'Vào lúc', 'Rời lúc', 'Phút tham gia', 'Số lần vào', 'Phút đi muộn', 'Điểm tương tác'], ',', '"', '');
            foreach ($rows as $row) {
                fputcsv($stream, [$row['name'], $row['email'], $row['status'], $row['joined_at']?->format('d/m/Y H:i:s'), $row['left_at']?->format('d/m/Y H:i:s'), (int) round($row['total_seconds'] / 60), $row['join_count'], $row['late_minutes'], $row['engagement_score']], ',', '"', '');
            }
            fclose($stream);
        }, 'live-attendance-'.$liveSession->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
