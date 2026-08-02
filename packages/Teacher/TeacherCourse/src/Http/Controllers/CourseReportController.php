<?php

namespace Mindigo\TeacherCourse\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Mindigo\TeacherCourse\Http\Requests\CourseAnalyticsRequest;
use Mindigo\TeacherCourse\Services\CourseReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseReportController extends Controller
{
    public function __invoke(CourseAnalyticsRequest $request, CourseReportService $reports): Response|StreamedResponse
    {
        $data = $request->validated();

        return $reports->export($request->user(), $data['scope'] ?? 'teacher', $data['entity_id'] ?? null, $data['format'] ?? 'csv');
    }
}
