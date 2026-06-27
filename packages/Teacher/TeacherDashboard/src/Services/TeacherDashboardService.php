<?php

namespace Mindigo\TeacherDashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\TeacherAssignment\Models\Assignment;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;

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

    public function getAssignmentStats(User $teacher): array
    {
        $total     = Assignment::where('teacher_id', $teacher->id)->count();
        $published = Assignment::where('teacher_id', $teacher->id)->where('status', 'published')->count();
        $draft     = Assignment::where('teacher_id', $teacher->id)->where('status', 'draft')->count();
        $pendingSubmissions = AssignmentSubmission::whereHas('assignment', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->whereNull('graded_at')
            ->count();

        return compact('total', 'published', 'draft', 'pendingSubmissions');
    }

    public function getRecentAssignments(User $teacher, int $limit = 4)
    {
        return Assignment::where('teacher_id', $teacher->id)
            ->with(['classroom:id,name'])
            ->withCount('submissions')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getRecentAssignmentSubmissions(User $teacher, int $limit = 5): \Illuminate\Support\Collection
    {
        return AssignmentSubmission::with(['assignment:id,title,teacher_id,classroom_id,max_score', 'assignment.classroom:id,name', 'student:id,name'])
            ->whereHas('assignment', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->latest('submitted_at')
            ->limit($limit)
            ->get();
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

    public function getUpcomingActivities(User $teacher, int $limit = 5): \Illuminate\Support\Collection
    {
        $assignments = Assignment::where('teacher_id', $teacher->id)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now()->startOfDay())
            ->with('classroom:id,name')
            ->orderBy('due_date')
            ->limit($limit)
            ->get()
            ->map(fn ($assignment) => (object) [
                'type' => 'assignment',
                'title' => $assignment->title,
                'subtitle' => $assignment->classroom?->name ?? 'Bài tập',
                'time' => $assignment->due_date,
                'route' => Route::has('teacher.assignments.submissions.index')
                    ? route('teacher.assignments.submissions.index', $assignment)
                    : '#',
            ]);

        $exams = Exam::where('created_by', $teacher->id)
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->limit($limit)
            ->get(['id', 'title', 'subject', 'starts_at'])
            ->map(fn ($exam) => (object) [
                'type' => 'exam',
                'title' => $exam->title,
                'subtitle' => $exam->subject ?: 'Đề thi',
                'time' => $exam->starts_at,
                'route' => Route::has('teacher.exams.show')
                    ? route('teacher.exams.show', $exam)
                    : '#',
            ]);

        return $assignments
            ->concat($exams)
            ->sortBy('time')
            ->take($limit)
            ->values();
    }

    public function getPerformanceOverview(User $teacher): array
    {
        $rows = Classroom::where('teacher_id', $teacher->id)
            ->withCount('students')
            ->latest()
            ->limit(5)
            ->get(['id', 'name']);

        $labels = [];
        $averages = [];
        $studentCounts = [];

        foreach ($rows as $classroom) {
            $labels[] = $classroom->name;
            $studentCounts[] = (int) $classroom->students_count;

            $studentIds = DB::table('classroom_students')
                ->where('classroom_id', $classroom->id)
                ->pluck('student_id');

            $average = ExamAttempt::whereIn('user_id', $studentIds)
                ->whereHas('exam', fn ($q) => $q->where('created_by', $teacher->id))
                ->where('status', 'submitted')
                ->avg('percentage');

            $averages[] = $average ? round($average, 1) : 0;
        }

        return compact('labels', 'averages', 'studentCounts');
    }
}
