<?php

namespace Mindigo\ExamManagement\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Services\ExamAnalyticsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamAnalyticsController extends Controller
{
    public function __construct(private readonly ExamAnalyticsService $analytics) {}

    public function show(Request $request, ExamSession $session): View
    {
        return view('Mindigo-exam-management::analytics.show', $this->analytics->report($session, $request->user()));
    }

    public function export(Request $request, ExamSession $session, string $format): Response|StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);
        $report = $this->analytics->report($session, $request->user());
        $filename = 'mindigo-exam-analytics-'.$session->slug.'-'.now()->format('Ymd-His');
        if ($format === 'pdf') {
            return Pdf::loadView('Mindigo-exam-management::analytics.pdf', $report)->download($filename.'.pdf');
        }

        return response()->streamDownload(function () use ($report): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, [__('Mindigo-exam-management::app.analytics.question'), __('Mindigo-exam-management::app.analytics.type'), __('Mindigo-exam-management::app.analytics.responses'), __('Mindigo-exam-management::app.analytics.correct_rate'), __('Mindigo-exam-management::app.analytics.blank_rate'), __('Mindigo-exam-management::app.analytics.average_time'), __('Mindigo-exam-management::app.analytics.flagged')]);
            foreach ($report['questions'] as $row) {
                fputcsv($stream, [strip_tags($row['question']->content), $row['question']->type, $row['responses'], $row['correct_rate'].'%', $row['blank_rate'].'%', $row['average_seconds'], $row['flagged'] ? __('Mindigo-exam-management::app.yes') : __('Mindigo-exam-management::app.no')]);
            }
            fclose($stream);
        }, $filename.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function operations(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('Mindigo-exam-management::analytics.operations', ['operations' => $this->analytics->operational()]);
    }
}
