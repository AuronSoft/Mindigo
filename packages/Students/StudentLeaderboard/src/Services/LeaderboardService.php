<?php

namespace Mindigo\StudentLeaderboard\Services;

use Illuminate\Support\Collection;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\TeacherAssignment\Models\AssignmentSubmission;

class LeaderboardService
{
    /**
     * IDs of the classrooms the student is actively enrolled in
     * (soft-deleted classrooms are excluded by the default Eloquent scope).
     */
    public function classroomIdsForStudent(int $studentId): Collection
    {
        return Classroom::whereHas('students', function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
                ->where('classroom_students.status', 'active');
        })->pluck('id');
    }

    /**
     * The student's classrooms (id, name, code) for the filter dropdown.
     */
    public function getClassroomsForStudent(int $studentId): Collection
    {
        return Classroom::whereHas('students', function ($q) use ($studentId) {
            $q->where('student_id', $studentId)
                ->where('classroom_students.status', 'active');
        })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Build the ranking.
     *
     * @return array{rows: Collection, me: ?object, total: int}
     */
    public function ranking(User $student, ?int $classroomId = null): array
    {
        $studentId = (int) $student->id;
        $myClassroomIds = $this->classroomIdsForStudent($studentId);

        // Decide which classrooms define the candidate pool.
        if ($classroomId !== null && $myClassroomIds->contains($classroomId)) {
            $classroomIds = collect([$classroomId]);
        } else {
            $classroomIds = $myClassroomIds;
        }

        // Collect the active members across the chosen classrooms.
        $candidates = collect();
        if ($classroomIds->isNotEmpty()) {
            $classrooms = Classroom::with(['students' => function ($q) {
                $q->wherePivot('status', 'active');
            }])->whereIn('id', $classroomIds)->get();

            foreach ($classrooms as $classroom) {
                foreach ($classroom->students as $member) {
                    $candidates->put((int) $member->id, $member);
                }
            }
        }

        // Always include the current student.
        $candidates->put($studentId, $student);

        $userIds = $candidates->keys()->all();

        // Aggregate scored work in two grouped queries (no N+1).

        // 1) Submitted exam attempts -> percentages keyed by user.
        $examPercents = [];
        ExamAttempt::whereIn('user_id', $userIds)
            ->whereNotNull('submitted_at')
            ->whereNotNull('percentage')
            ->get(['user_id', 'percentage'])
            ->each(function ($a) use (&$examPercents) {
                $examPercents[(int) $a->user_id][] = (float) $a->percentage;
            });

        // 2) Graded assignment submissions -> percentages keyed by student.
        $assignPercents = [];
        AssignmentSubmission::with('assignment:id,max_score')
            ->whereIn('student_id', $userIds)
            ->whereIn('status', ['graded', 'returned'])
            ->whereNotNull('score')
            ->get()
            ->each(function ($s) use (&$assignPercents) {
                $pct = $s->scorePercent();
                if (! is_null($pct)) {
                    $assignPercents[(int) $s->student_id][] = (float) $pct;
                }
            });

        // Build a row per candidate.
        $rows = $candidates->map(function (User $member) use ($examPercents, $assignPercents, $studentId) {
            $uid = (int) $member->id;
            $percents = array_merge($examPercents[$uid] ?? [], $assignPercents[$uid] ?? []);
            $completed = count($percents);
            $hasData = $completed > 0;
            $score = $hasData ? round(array_sum($percents) / $completed, 1) : 0.0;

            return (object) [
                'rank' => 0,
                'id' => $uid,
                'name' => $member->name,
                'score' => $score,
                'completed' => $completed,
                'has_data' => $hasData,
                'is_me' => $uid === $studentId,
            ];
        })->values();

        // Sort: score desc, completed desc, name asc.
        $rows = $rows->sort(function ($a, $b) {
            return [$b->score, $b->completed, $a->name] <=> [$a->score, $a->completed, $b->name];
        })->values();

        // Assign sequential 1-based rank.
        $rows = $rows->map(function ($row, $i) {
            $row->rank = $i + 1;

            return $row;
        });

        $me = $rows->firstWhere('is_me', true);

        return [
            'rows' => $rows,
            'me' => $me,
            'total' => $rows->count(),
        ];
    }
}
