<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\ExamManagement\Models\ExamSessionAttemptAnswer;
use Mindigo\LearningTools\Models\MistakeReview;
use Mindigo\LearningTools\Services\LearningAnalyticsService;
use Mindigo\StudentPractice\Models\PracticeAnswer;

class MistakeNotebookController extends Controller
{
    public function index(Request $request, LearningAnalyticsService $analytics): View
    {
        $mistakes = $analytics->mistakes($request->user()->getAuthIdentifier())
            ->when($request->filled('subject'), fn ($items) => $items->where('subject', $request->string('subject')))
            ->when($request->boolean('unresolved'), fn ($items) => $items->filter(fn ($row) => ! $row['review']?->is_resolved));

        return view('learning-tools::mistakes.index', [
            'mistakes' => $mistakes,
            'subjects' => $mistakes->pluck('subject')->filter()->unique()->sort()->values(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_type' => ['required', 'in:practice,exam,exam_session'],
            'source_answer_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:2000'],
            'is_resolved' => ['nullable', 'boolean'],
        ]);
        $owned = match ($data['source_type']) {
            'practice' => PracticeAnswer::whereKey($data['source_answer_id'])->whereHas('attempt', fn ($query) => $query->where('student_id', $request->user()->getAuthIdentifier()))->exists(),
            'exam' => ExamAttemptAnswer::whereKey($data['source_answer_id'])->whereHas('attempt', fn ($query) => $query->where('user_id', $request->user()->getAuthIdentifier()))->exists(),
            'exam_session' => ExamSessionAttemptAnswer::whereKey($data['source_answer_id'])->whereHas('attempt', fn ($query) => $query->where('user_id', $request->user()->getAuthIdentifier()))->exists(),
        };
        abort_unless($owned, 403);

        MistakeReview::updateOrCreate(
            ['user_id' => $request->user()->getAuthIdentifier(), 'source_type' => $data['source_type'], 'source_answer_id' => $data['source_answer_id']],
            ['note' => $data['note'] ?? null, 'is_resolved' => $request->boolean('is_resolved'), 'last_reviewed_at' => now()]
        )->increment('review_count');

        return back()->with('success', __('learning-tools::app.mistakes.updated'));
    }
}
