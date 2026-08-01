<?php

namespace Mindigo\StudentPractice\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\StudentPractice\Http\Requests\PracticeAnalyticsRequest;
use Mindigo\StudentPractice\Services\PracticeAnalyticsService;

class PracticeAnalyticsController extends Controller
{
    public function __construct(private readonly PracticeAnalyticsService $analytics) {}

    public function index(PracticeAnalyticsRequest $request): View
    {
        $filters = $request->validated();
        $filters['period'] ??= config('practice.analytics.default_period');

        return view('student-practice::analytics.index', $this->analytics->dashboard($request->user(), $filters));
    }
}
