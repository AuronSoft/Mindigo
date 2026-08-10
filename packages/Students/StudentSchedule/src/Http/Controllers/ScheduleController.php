<?php

namespace Mindigo\StudentSchedule\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Mindigo\Auth\Models\User;
use Mindigo\StudentSchedule\Http\Requests\StudentScheduleIndexRequest;
use Mindigo\StudentSchedule\Services\ScheduleService;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleService $service) {}

    public function index(StudentScheduleIndexRequest $request): View
    {
        /** @var User $student */
        $student = $request->user();
        $validated = $request->validated();
        $anchor = CarbonImmutable::createFromFormat('Y-m-d', $validated['date'] ?? now()->toDateString(), config('app.timezone'));

        return view('student-schedule::index', [
            ...$this->service->workspace($student, $anchor, $validated['view'] ?? 'week', $validated),
            'filters' => $validated,
        ]);
    }
}
