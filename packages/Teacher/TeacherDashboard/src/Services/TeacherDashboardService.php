<?php

namespace Mindigo\TeacherDashboard\Services;

use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\QuestionBank\Models\Question;

class TeacherDashboardService
{
    public function getStats(User $teacher): array
    {
        $totalClassrooms = Classroom::where('teacher_id', $teacher->id)->count();

        $totalStudents = DB::table('classroom_students')
            ->join('classrooms', 'classrooms.id', '=', 'classroom_students.classroom_id')
            ->where('classrooms.teacher_id', $teacher->id)
            ->whereNull('classrooms.deleted_at')
            ->where('classroom_students.status', 'active')
            ->distinct('classroom_students.student_id')
            ->count('classroom_students.student_id');

        $totalExams     = Exam::where('created_by', $teacher->id)->count();
        $publishedExams = Exam::where('created_by', $teacher->id)->where('status', 'published')->count();
        $draftExams     = Exam::where('created_by', $teacher->id)->where('status', 'draft')->count();

        $totalAttempts = ExamAttempt::whereHas('exam', fn ($q) => $q->where('created_by', $teacher->id))
            ->where('status', 'submitted')->count();

        $passedAttempts = ExamAttempt::whereHas('exam', fn ($q) => $q->where('created_by', $teacher->id))
            ->where('status', 'submitted')->where('passed', true)->count();

        $totalQuestions   = Question::where('created_by', $teacher->id)->count();
        $pendingQuestions = Question::where('created_by', $teacher->id)->where('status', 'reviewing')->count();

        return compact(
            'totalClassrooms', 'totalStudents',
            'totalExams', 'publishedExams', 'draftExams',
            'totalAttempts', 'passedAttempts',
            'totalQuestions', 'pendingQuestions'
        );
    }

    public function getMyClassrooms(User $teacher, int $limit = 5): \Illuminate\Support\Collection
    {
        return Classroom::where('teacher_id', $teacher->id)
            ->withCount('students')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRecentExams(User $teacher, int $limit = 5): \Illuminate\Support\Collection
    {
        return Exam::where('created_by', $teacher->id)
            ->withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRecentAttempts(User $teacher, int $limit = 8): \Illuminate\Support\Collection
    {
        return ExamAttempt::with(['exam:id,title,subject', 'user:id,name'])
            ->whereHas('exam', fn ($q) => $q->where('created_by', $teacher->id))
            ->where('status', 'submitted')
            ->latest('submitted_at')
            ->limit($limit)
            ->get();
    }

    public function getTopStudents(User $teacher, int $limit = 5): \Illuminate\Support\Collection
    {
        return DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->where('exams.created_by', $teacher->id)
            ->where('exam_attempts.status', 'submitted')
            ->selectRaw('users.id, users.name, COUNT(*) as attempt_count, ROUND(AVG(exam_attempts.percentage), 1) as avg_score')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('avg_score')
            ->limit($limit)
            ->get();
    }

    public function getWeeklyTrend(User $teacher): array
    {
        $rows = ExamAttempt::whereHas('exam', fn ($q) => $q->where('created_by', $teacher->id))
            ->where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date     = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->locale('vi')->isoFormat('dd');
            $counts[] = $rows[$date]->count ?? 0;
        }

        return compact('labels', 'counts');
    }
}
