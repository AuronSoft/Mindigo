<?php

namespace Mindigo\TeacherLiveSession\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\TeacherLiveSession\Http\Requests\LiveSessionReportRequest;
use Mindigo\TeacherLiveSession\Services\LiveSessionReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LiveSessionReportController extends Controller
{
    public function __construct(private readonly LiveSessionReportService $reports) {}

    public function index(LiveSessionReportRequest $request): View
    {
        $filters = $request->validated();

        return view('teacher-live-session::reports.index', [
            'report' => $this->reports->report($request->user(), $filters),
            'filters' => $filters,
        ]);
    }

    public function export(LiveSessionReportRequest $request): Response|StreamedResponse
    {
        return $this->reports->export($request->user(), $request->validated());
    }
}
