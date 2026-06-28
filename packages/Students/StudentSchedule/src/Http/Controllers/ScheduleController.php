<?php

namespace Mindigo\StudentSchedule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Mindigo\StudentSchedule\Services\ScheduleService;

class ScheduleController extends Controller
{
    public function __construct(protected ScheduleService $service)
    {
    }

    public function index(Request $request)
    {
        $studentId = auth()->id();

        // Tháng đang xem (?month=YYYY-MM), mặc định tháng hiện tại
        $month = $this->resolveMonth($request->input('month'));

        $calendar = $this->service->buildCalendar($studentId, $month);
        $upcoming = $this->service->upcoming($studentId);

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        return view('student-schedule::index', compact('calendar', 'upcoming', 'month', 'prevMonth', 'nextMonth'));
    }

    private function resolveMonth(?string $value): Carbon
    {
        if ($value && preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
            } catch (\Throwable) {
                // ignore, fall back to current month
            }
        }

        return now()->startOfMonth();
    }
}
