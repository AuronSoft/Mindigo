<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\LearningTools\Services\LearningAnalyticsService;

class KnowledgeGapController extends Controller
{
    public function index(Request $request, LearningAnalyticsService $analytics): View
    {
        $user = $request->user();
        $students = collect([$user]);
        $classrooms = collect();
        if ($user->role === 'teacher') {
            $classrooms = Classroom::with('students')->where('teacher_id', $user->getAuthIdentifier())->orderBy('name')->get();
            $selected = $classrooms->firstWhere('id', $request->integer('classroom'));
            $students = $selected?->students ?? collect();
        }
        $gaps = $students->flatMap(fn ($student) => $analytics->gapsForStudent($student->id))
            ->groupBy(fn ($row) => ($row['subject'] ?: '-').'|'.($row['topic'] ?: '-'))
            ->map(function ($rows): array {
                $total = $rows->sum('total');
                $correct = $rows->sum('correct_count');
                $rate = $total ? round($correct / $total * 100, 1) : 0;

                return [...$rows->first(), 'total' => $total, 'correct_count' => $correct, 'rate' => $rate, 'level' => $rate < 50 ? 'weak' : ($rate < 75 ? 'average' : 'strong')];
            })->sortBy('rate')->values();

        return view('learning-tools::gaps.index', compact('gaps', 'classrooms'));
    }
}
