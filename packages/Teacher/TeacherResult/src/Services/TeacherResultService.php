<?php

namespace Mindigo\TeacherResult\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\ExamManagement\Models\ExamAttempt;

class TeacherResultService
{
    /**
     * Tổng quan kết quả toàn bộ đề thi của giáo viên.
     */
    public function overview(User $teacher): array
    {
        $tid = $teacher->getAuthIdentifier();

        $totalAttempts = ExamAttempt::whereHas('exam', fn ($q) => $q->where('created_by', $tid))
            ->where('status', 'submitted')->count();

        $passedAttempts = ExamAttempt::whereHas('exam', fn ($q) => $q->where('created_by', $tid))
            ->where('status', 'submitted')->where('passed', true)->count();

        $avgScore = ExamAttempt::whereHas('exam', fn ($q) => $q->where('created_by', $tid))
            ->where('status', 'submitted')->avg('percentage') ?? 0;

        $totalExams = Exam::where('created_by', $tid)->count();
        $totalStudents = DB::table('classroom_students')
            ->join('classrooms', 'classrooms.id', '=', 'classroom_students.classroom_id')
            ->where('classrooms.teacher_id', $tid)
            ->whereNull('classrooms.deleted_at')
            ->distinct('classroom_students.student_id')
            ->count('classroom_students.student_id');

        // Trend 14 ngày
        $trend = ExamAttempt::whereHas('exam', fn ($q) => $q->where('created_by', $tid))
            ->where('status', 'submitted')
            ->where('submitted_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(submitted_at) as date, COUNT(*) as count, ROUND(AVG(percentage),1) as avg_score')
            ->groupBy('date')->orderBy('date')
            ->get()->keyBy('date');

        $trendData = collect(range(13, 0))->map(function ($i) use ($trend) {
            $date = now()->subDays($i)->toDateString();
            return [
                'label'     => now()->subDays($i)->locale('vi')->isoFormat('D/M'),
                'count'     => $trend[$date]->count ?? 0,
                'avg_score' => $trend[$date]->avg_score ?? null,
            ];
        });

        return [
            'total_attempts' => $totalAttempts,
            'passed_attempts'=> $passedAttempts,
            'pass_rate'      => $totalAttempts > 0 ? round($passedAttempts / $totalAttempts * 100, 1) : 0,
            'avg_score'      => round($avgScore, 1),
            'total_exams'    => $totalExams,
            'total_students' => $totalStudents,
            'trend'          => $trendData,
        ];
    }

    /**
     * Kết quả từng đề thi của giáo viên, kèm stats.
     */
    public function examResults(User $teacher, string $keyword = ''): Collection
    {
        $query = Exam::where('created_by', $teacher->getAuthIdentifier())
            ->withCount(['attempts' => fn ($q) => $q->where('status', 'submitted')])
            ->withAvg(['attempts' => fn ($q) => $q->where('status', 'submitted')], 'percentage')
            ->orderByDesc('updated_at');

        if ($keyword) {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$keyword}%")->orWhere('subject', 'like', "%{$keyword}%"));
        }

        return $query->limit(20)->get()->map(function (Exam $exam) {
            $passed = ExamAttempt::where('exam_id', $exam->id)->where('status', 'submitted')->where('passed', true)->count();

            return [
                'exam'       => $exam,
                'attempts'   => $exam->attempts_count,
                'avg_score'  => round($exam->attempts_avg_percentage ?? 0, 1),
                'pass_rate'  => $exam->attempts_count > 0 ? round($passed / $exam->attempts_count * 100, 1) : 0,
                'passed'     => $passed,
            ];
        });
    }

    /**
     * Danh sách học sinh trong các lớp của giáo viên, kèm thống kê.
     */
    public function studentResults(User $teacher, string $keyword = ''): Collection
    {
        $tid = $teacher->getAuthIdentifier();

        // Lấy IDs học sinh trong lớp của giáo viên
        $studentIds = DB::table('classroom_students')
            ->join('classrooms', 'classrooms.id', '=', 'classroom_students.classroom_id')
            ->where('classrooms.teacher_id', $tid)
            ->whereNull('classrooms.deleted_at')
            ->pluck('classroom_students.student_id')
            ->unique();

        $query = User::students()
            ->whereIn('id', $studentIds)
            ->select('id', 'name', 'email');

        if ($keyword) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"));
        }

        return $query->limit(30)->get()->map(function (User $student) use ($tid) {
            $attempts = ExamAttempt::where('user_id', $student->id)
                ->whereHas('exam', fn ($q) => $q->where('created_by', $tid))
                ->where('status', 'submitted');

            $total  = (clone $attempts)->count();
            $passed = (clone $attempts)->where('passed', true)->count();
            $avg    = (clone $attempts)->avg('percentage') ?? 0;
            $last   = (clone $attempts)->latest('submitted_at')->value('submitted_at');

            return [
                'student'    => $student,
                'total'      => $total,
                'passed'     => $passed,
                'pass_rate'  => $total > 0 ? round($passed / $total * 100) : 0,
                'avg_score'  => round($avg, 1),
                'last_at'    => $last,
            ];
        })->sortByDesc('avg_score')->values();
    }

    /**
     * Chi tiết lịch sử làm bài của 1 học sinh trong đề thi của giáo viên.
     */
    public function studentDetail(User $teacher, User $student): array
    {
        $tid = $teacher->getAuthIdentifier();

        $history = ExamAttempt::where('user_id', $student->id)
            ->whereHas('exam', fn ($q) => $q->where('created_by', $tid))
            ->where('status', 'submitted')
            ->with('exam:id,title,subject')
            ->orderByDesc('submitted_at')
            ->get();

        $bySubject = $history->whereNotNull('exam.subject')
            ->groupBy(fn ($a) => $a->exam->subject)
            ->map(fn ($g) => [
                'subject'   => $g->first()->exam->subject,
                'count'     => $g->count(),
                'avg_score' => round($g->avg('percentage'), 1),
                'pass_rate' => round($g->where('passed', true)->count() / max(1, $g->count()) * 100),
            ])->values();

        return [
            'history'    => $history,
            'by_subject' => $bySubject,
            'total'      => $history->count(),
            'avg_score'  => round($history->avg('percentage') ?? 0, 1),
            'pass_rate'  => $history->count() > 0 ? round($history->where('passed', true)->count() / $history->count() * 100, 1) : 0,
        ];
    }

    /**
     * Chi tiết kết quả 1 đề thi.
     */
    public function examDetail(User $teacher, Exam $exam): array
    {
        abort_unless($exam->created_by === (int) $teacher->getAuthIdentifier() || $teacher->isAdmin(), 403);

        $attempts = ExamAttempt::where('exam_id', $exam->id)->where('status', 'submitted');
        $total    = (clone $attempts)->count();
        $passed   = (clone $attempts)->where('passed', true)->count();
        $avgScore = (clone $attempts)->avg('percentage') ?? 0;

        $distribution = [];
        foreach (['0–20' => [0,20], '20–40' => [20,40], '40–60' => [40,60], '60–80' => [60,80], '80–100' => [80,101]] as $label => [$min, $max]) {
            $distribution[$label] = (clone $attempts)->where('percentage', '>=', $min)->where('percentage', '<', $max)->count();
        }

        $list = (clone $attempts)->with('user:id,name,email')->orderByDesc('percentage')->limit(50)->get();

        return [
            'total'        => $total,
            'passed'       => $passed,
            'failed'       => $total - $passed,
            'pass_rate'    => $total > 0 ? round($passed / $total * 100, 1) : 0,
            'avg_score'    => round($avgScore, 1),
            'distribution' => $distribution,
            'list'         => $list,
        ];
    }

    public function getMyClassrooms(User $teacher): Collection
    {
        return Classroom::where('teacher_id', $teacher->getAuthIdentifier())
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }
}
