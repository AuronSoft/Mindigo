<?php

namespace Mindigo\StudentHistory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\StudentHistory\Services\HistoryService;

class HistoryController extends Controller
{
    public function __construct(protected HistoryService $service)
    {
    }

    public function index(Request $request)
    {
        $studentId = auth()->id();

        $type = $request->input('type');
        if (! in_array($type, ['assignment', 'exam'], true)) {
            $type = null;
        }

        $items   = $this->service->timeline($studentId, $type);
        $summary = $this->service->summary($studentId);

        return view('student-history::index', compact('items', 'summary', 'type'));
    }
}
