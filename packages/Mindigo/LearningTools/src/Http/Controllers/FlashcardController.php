<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\FlashcardDeckRequest;
use Mindigo\LearningTools\Http\Requests\FlashcardRequest;
use Mindigo\LearningTools\Http\Requests\FlashcardReviewRequest;
use Mindigo\LearningTools\Models\Flashcard;
use Mindigo\LearningTools\Models\FlashcardDeck;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherClassroom\Models\Classroom;

class FlashcardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = FlashcardDeck::with(['owner', 'subject'])->withCount('cards');

        if ($user->role === 'student') {
            $query->where(function (Builder $builder) use ($user): void {
                $builder->where('owner_id', $user->getAuthIdentifier())
                    ->orWhere('visibility', 'public')
                    ->orWhereHas('classrooms.students', fn (Builder $students) => $students->whereKey($user->getAuthIdentifier()));
            });
        } else {
            $query->where(fn (Builder $builder) => $builder->where('owner_id', $user->getAuthIdentifier())->orWhere('visibility', 'public'));
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->string('q').'%');
        }
        if ($request->filled('subject')) {
            $query->where('subject_id', $request->integer('subject'));
        }

        return view('learning-tools::flashcards.index', [
            'decks' => $query->latest()->paginate(12)->withQueryString(),
            'subjects' => $this->subjects(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('learning-tools::flashcards.form', [
            'deck' => new FlashcardDeck,
            'subjects' => $this->subjects(),
            'classrooms' => $this->classrooms($request),
        ]);
    }

    public function store(FlashcardDeckRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('classroom_ids');
        $deck = FlashcardDeck::create([...$data, 'owner_id' => $request->user()->getAuthIdentifier()]);
        $this->syncClassrooms($request, $deck);

        return to_route('learning-tools.flashcards.show', $deck)->with('success', __('learning-tools::app.flashcards.created'));
    }

    public function show(Request $request, FlashcardDeck $deck): View
    {
        $this->authorizeView($request, $deck);
        $deck->load(['cards', 'subject', 'owner', 'classrooms']);

        return view('learning-tools::flashcards.show', compact('deck'));
    }

    public function edit(Request $request, FlashcardDeck $deck): View
    {
        $this->authorizeOwner($request, $deck);
        $deck->load('classrooms');

        return view('learning-tools::flashcards.form', [
            'deck' => $deck,
            'subjects' => $this->subjects(),
            'classrooms' => $this->classrooms($request),
        ]);
    }

    public function update(FlashcardDeckRequest $request, FlashcardDeck $deck): RedirectResponse
    {
        $this->authorizeOwner($request, $deck);
        $deck->update($request->safe()->except('classroom_ids'));
        $this->syncClassrooms($request, $deck);

        return to_route('learning-tools.flashcards.show', $deck)->with('success', __('learning-tools::app.flashcards.updated'));
    }

    public function destroy(Request $request, FlashcardDeck $deck): RedirectResponse
    {
        $this->authorizeOwner($request, $deck);
        $deck->delete();

        return to_route('learning-tools.flashcards.index')->with('success', __('learning-tools::app.flashcards.deleted'));
    }

    public function storeCard(FlashcardRequest $request, FlashcardDeck $deck): RedirectResponse
    {
        $this->authorizeOwner($request, $deck);
        $deck->cards()->create([...$request->validated(), 'position' => ($deck->cards()->max('position') ?? 0) + 1]);

        return back()->with('success', __('learning-tools::app.flashcards.card_created'));
    }

    public function destroyCard(Request $request, FlashcardDeck $deck, Flashcard $card): RedirectResponse
    {
        $this->authorizeOwner($request, $deck);
        abort_unless((int) $card->flashcard_deck_id === (int) $deck->id, 404);
        $card->delete();

        return back()->with('success', __('learning-tools::app.flashcards.card_deleted'));
    }

    public function study(Request $request, FlashcardDeck $deck): View
    {
        $this->authorizeView($request, $deck);
        $deck->load('cards');

        return view('learning-tools::flashcards.study', compact('deck'));
    }

    public function review(FlashcardReviewRequest $request, FlashcardDeck $deck, Flashcard $card): RedirectResponse
    {
        $this->authorizeView($request, $deck);
        abort_unless((int) $card->flashcard_deck_id === (int) $deck->id, 404);
        $interval = match ($request->validated('rating')) {
            'again' => 0,
            'hard' => 1,
            'good' => 3,
            'easy' => 7,
        };
        $existing = $card->learners()->whereKey($request->user()->getAuthIdentifier())->first()?->pivot;
        $card->learners()->syncWithoutDetaching([
            $request->user()->getAuthIdentifier() => [
                'rating' => $request->validated('rating'),
                'repetitions' => ($existing?->repetitions ?? 0) + 1,
                'interval_days' => $interval,
                'last_reviewed_at' => now(),
                'next_review_at' => now()->addDays($interval),
            ],
        ]);

        return back()->with('success', __('learning-tools::app.flashcards.review_saved'));
    }

    private function authorizeView(Request $request, FlashcardDeck $deck): void
    {
        $user = $request->user();
        $allowed = (int) $deck->owner_id === (int) $user->getAuthIdentifier()
            || $deck->visibility === 'public'
            || ($user->role === 'student' && $deck->classrooms()->whereHas('students', fn (Builder $query) => $query->whereKey($user->getAuthIdentifier()))->exists());
        abort_unless($allowed, 403);
    }

    private function authorizeOwner(Request $request, FlashcardDeck $deck): void
    {
        abort_unless($request->user()->role === 'admin' || (int) $deck->owner_id === (int) $request->user()->getAuthIdentifier(), 403);
    }

    private function syncClassrooms(Request $request, FlashcardDeck $deck): void
    {
        if ($request->user()->role !== 'teacher') {
            $deck->classrooms()->detach();

            return;
        }
        $allowedIds = $this->classrooms($request)->pluck('id');
        $ids = collect($request->validated('classroom_ids', []))->intersect($allowedIds);
        $deck->classrooms()->sync($ids->mapWithKeys(fn ($id) => [$id => [
            'assigned_by' => $request->user()->getAuthIdentifier(),
            'assigned_at' => now(),
        ]])->all());
    }

    private function subjects()
    {
        return Subject::where('status', 'active')->orderBy('name')->get();
    }

    private function classrooms(Request $request)
    {
        return $request->user()->role === 'teacher'
            ? Classroom::where('teacher_id', $request->user()->getAuthIdentifier())->where('status', 'active')->orderBy('name')->get()
            : collect();
    }
}
