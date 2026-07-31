<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\ExamManagement\Models\ExamAttemptAnswer;
use Mindigo\LearningTools\Http\Requests\PersonalizedPracticeSetRequest;
use Mindigo\LearningTools\Models\PersonalizedPracticeSet;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\StudentPractice\Models\PracticeAnswer;
use Mindigo\StudentPractice\Models\PracticeAttempt;

class PersonalizedPracticeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $sets = PersonalizedPracticeSet::with(['creator', 'classroom'])->withCount('questions')
            ->when($user->role === 'student', fn (Builder $query) => $query->where(function (Builder $scope) use ($user): void {
                $scope->where('creator_id', $user->getAuthIdentifier())
                    ->orWhereHas('classroom.students', fn (Builder $students) => $students->whereKey($user->getAuthIdentifier()));
            }), fn (Builder $query) => $query->where('creator_id', $user->getAuthIdentifier()))
            ->latest()->paginate(12);

        return view('learning-tools::personalized.index', compact('sets'));
    }

    public function create(Request $request): View
    {
        return view('learning-tools::personalized.form', [
            'classrooms' => $this->classrooms($request),
            'subjects' => Question::where('status', 'approved')->whereNotNull('subject')->distinct()->orderBy('subject')->pluck('subject'),
        ]);
    }

    public function store(PersonalizedPracticeSetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->validateClassroomScope($request, $data['classroom_id'] ?? null);
        $questions = $this->questionQuery($request, $data)->inRandomOrder()->limit($data['question_count'])->get();
        if ($questions->isEmpty()) {
            return back()->withInput()->withErrors(['question_count' => __('learning-tools::app.personalized.no_questions')]);
        }
        $set = DB::transaction(function () use ($request, $data, $questions): PersonalizedPracticeSet {
            $set = PersonalizedPracticeSet::create([...$data, 'creator_id' => $request->user()->getAuthIdentifier()]);
            $set->questions()->attach($questions->values()->mapWithKeys(fn ($question, $index) => [$question->id => ['position' => $index + 1]])->all());

            return $set;
        });

        return to_route('learning-tools.personalized.show', $set)->with('success', __('learning-tools::app.personalized.created'));
    }

    public function show(Request $request, PersonalizedPracticeSet $set): View
    {
        $this->authorizeView($request, $set);
        $set->load(['creator', 'classroom', 'questions']);

        return view('learning-tools::personalized.show', compact('set'));
    }

    public function start(Request $request, PersonalizedPracticeSet $set): RedirectResponse
    {
        abort_unless($request->user()->role === 'student', 403);
        $this->authorizeView($request, $set);
        $set->load('questions');
        abort_if($set->questions->isEmpty(), 422);
        $attempt = DB::transaction(function () use ($request, $set): PracticeAttempt {
            $attempt = PracticeAttempt::create([
                'student_id' => $request->user()->getAuthIdentifier(), 'mode' => $set->topic ? 'topic' : ($set->subject ? 'subject' : 'mixed'),
                'subject' => $set->subject, 'topic' => $set->topic, 'difficulty' => $set->difficulty,
                'total_questions' => $set->questions->count(), 'correct_answers' => 0, 'started_at' => now(),
            ]);
            $attempt->answers()->createMany($set->questions->map(fn ($question) => ['question_id' => $question->id, 'student_answer' => null, 'is_correct' => false, 'points' => 0])->all());

            return $attempt;
        });

        return to_route('student.practice.attempt', $attempt);
    }

    public function destroy(Request $request, PersonalizedPracticeSet $set): RedirectResponse
    {
        abort_unless((int) $set->creator_id === (int) $request->user()->getAuthIdentifier(), 403);
        $set->delete();

        return to_route('learning-tools.personalized.index')->with('success', __('learning-tools::app.personalized.deleted'));
    }

    private function questionQuery(Request $request, array $data): Builder
    {
        $query = Question::query()->where('status', 'approved');
        foreach (['subject', 'topic', 'difficulty'] as $field) {
            if (filled($data[$field] ?? null)) {
                $query->where($field, $data[$field]);
            }
        }
        if ($data['source'] === 'mistakes') {
            $practiceIds = PracticeAnswer::where('is_correct', false)->whereHas('attempt', fn ($q) => $q->where('student_id', $request->user()->getAuthIdentifier())->whereNotNull('completed_at'))->pluck('question_id');
            $examIds = ExamAttemptAnswer::where('is_correct', false)->whereHas('attempt', fn ($q) => $q->where('user_id', $request->user()->getAuthIdentifier())->whereIn('status', ['submitted', 'expired']))->whereHas('question', fn ($q) => $q->whereNotNull('question_id'))->with('question:id,question_id')->get()->pluck('question.question_id');
            $query->whereIn('id', $practiceIds->concat($examIds)->unique());
        }

        return $query;
    }

    private function authorizeView(Request $request, PersonalizedPracticeSet $set): void
    {
        $user = $request->user();
        $allowed = (int) $set->creator_id === (int) $user->getAuthIdentifier()
            || ($user->role === 'student' && $set->classroom_id && $set->classroom?->students()->whereKey($user->getAuthIdentifier())->exists());
        abort_unless($allowed, 403);
    }

    private function validateClassroomScope(Request $request, mixed $classroomId): void
    {
        if (! $classroomId) {
            return;
        }
        abort_unless($request->user()->role === 'teacher' && Classroom::whereKey($classroomId)->where('teacher_id', $request->user()->getAuthIdentifier())->exists(), 403);
    }

    private function classrooms(Request $request)
    {
        return $request->user()->role === 'teacher' ? Classroom::where('teacher_id', $request->user()->getAuthIdentifier())->where('status', 'active')->orderBy('name')->get() : collect();
    }
}
