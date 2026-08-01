<?php

namespace Mindigo\StudentPractice\Services;

use Illuminate\Support\Facades\DB;
use Mindigo\Auth\Models\User;
use Mindigo\StudentPractice\Models\PracticeAttempt;
use Mindigo\StudentPractice\Models\PracticeSkill;
use Mindigo\StudentPractice\Models\StudentSkillProgress;

class SkillProgressService
{
    public function __construct(private readonly MasteryCalculator $mastery) {}

    public function catalog(User $student, array $filters): array
    {
        $query = PracticeSkill::query()->with(['subject:id,name', 'topic:id,name'])
            ->withCount(['questions' => fn ($query) => $query->practiceReady()])
            ->where('status', PracticeSkill::STATUS_ACTIVE);
        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$keyword}%")->orWhere('code', 'like', "%{$keyword}%"));
        }
        foreach (['subject_id', 'grade_level'] as $field) {
            if (filled($filters[$field] ?? null)) {
                $query->where($field, $filters[$field]);
            }
        }

        $skills = $query->orderBy('sort_order')->orderBy('name')->paginate(12)->withQueryString();
        $progress = StudentSkillProgress::query()->where('student_id', $student->getAuthIdentifier())
            ->whereIn('practice_skill_id', $skills->pluck('id'))->get()->keyBy('practice_skill_id');

        return ['skills' => $skills, 'progressBySkill' => $progress];
    }

    public function details(User $student, PracticeSkill $skill): array
    {
        $skill->loadMissing(['subject:id,name', 'topic:id,name'])->loadCount(['questions' => fn ($query) => $query->practiceReady()]);
        $progress = StudentSkillProgress::query()->where('student_id', $student->getAuthIdentifier())
            ->where('practice_skill_id', $skill->getKey())->first();
        $history = PracticeAttempt::query()->where('student_id', $student->getAuthIdentifier())
            ->where('practice_skill_id', $skill->getKey())->where('status', PracticeAttempt::STATUS_COMPLETED)
            ->latest('completed_at')->paginate(10);
        $difficultyCounts = DB::table('question_bank_questions as questions')
            ->join('question_practice_skill as mapping', 'mapping.question_id', '=', 'questions.id')
            ->where('mapping.practice_skill_id', $skill->getKey())->where('questions.practice_status', 'ready')
            ->selectRaw('questions.difficulty, COUNT(*) total')->groupBy('questions.difficulty')->pluck('total', 'difficulty');

        return compact('skill', 'progress', 'history', 'difficultyCounts');
    }

    public function rebuild(User $student, PracticeSkill $skill): StudentSkillProgress
    {
        $attempts = PracticeAttempt::query()->where('student_id', $student->getAuthIdentifier())
            ->where('practice_skill_id', $skill->getKey())->where('status', PracticeAttempt::STATUS_COMPLETED);
        $summary = (clone $attempts)->selectRaw('COUNT(*) attempts, COALESCE(SUM(total_questions), 0) questions, COALESCE(SUM(correct_answers), 0) correct_count, COALESCE(AVG(score), 0) average_score, COALESCE(MAX(score), 0) best_score')->firstOrFail();
        $questions = (int) $summary->questions;
        $seconds = (clone $attempts)->get(['started_at', 'completed_at'])->sum(
            fn (PracticeAttempt $attempt): int => $attempt->completed_at?->diffInSeconds($attempt->started_at) ?? 0
        );

        return StudentSkillProgress::query()->updateOrCreate(
            ['student_id' => $student->getAuthIdentifier(), 'practice_skill_id' => $skill->getKey()],
            [
                'completed_attempts' => (int) $summary->attempts,
                'total_questions' => $questions,
                'correct_answers' => (int) $summary->correct_count,
                'accuracy' => $questions > 0 ? round(((int) $summary->correct_count / $questions) * 100, 2) : 0,
                'average_score' => round((float) $summary->average_score, 2),
                'best_score' => round((float) $summary->best_score, 2),
                'practice_seconds' => $seconds,
                'last_practiced_at' => (clone $attempts)->max('completed_at'),
                ...$this->mastery->calculate($student, $skill),
            ]
        );
    }
}
