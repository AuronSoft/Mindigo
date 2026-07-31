<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\LearningNoteRequest;
use Mindigo\LearningTools\Models\LearningNote;
use Mindigo\SubjectManagement\Models\Subject;

class LearningNoteController extends Controller
{
    public function index(Request $request): View
    {
        $query = LearningNote::with(['subject', 'topic'])->where('owner_id', $request->user()->getAuthIdentifier())
            ->orderByDesc('is_pinned')->latest('updated_at');

        if ($request->filled('q')) {
            $query->where(fn ($builder) => $builder->where('title', 'like', '%'.$request->string('q').'%')
                ->orWhere('content', 'like', '%'.$request->string('q').'%'));
        }
        if ($request->filled('subject')) {
            $query->where('subject_id', $request->integer('subject'));
        }

        return view('learning-tools::notes.index', [
            'notes' => $query->paginate(12)->withQueryString(),
            'subjects' => Subject::with(['topics' => fn ($query) => $query->where('status', 'active')])
                ->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('learning-tools::notes.form', ['note' => new LearningNote, 'subjects' => $this->subjects()]);
    }

    public function store(LearningNoteRequest $request): RedirectResponse
    {
        $note = LearningNote::create([
            ...$request->validated(),
            'owner_id' => $request->user()->getAuthIdentifier(),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return to_route('learning-tools.notes.edit', $note)->with('success', __('learning-tools::app.notes.created'));
    }

    public function edit(Request $request, LearningNote $note): View
    {
        $this->authorizeOwner($request, $note);

        return view('learning-tools::notes.form', compact('note') + ['subjects' => $this->subjects()]);
    }

    public function update(LearningNoteRequest $request, LearningNote $note): RedirectResponse
    {
        $this->authorizeOwner($request, $note);
        $note->update([...$request->validated(), 'is_pinned' => $request->boolean('is_pinned')]);

        return back()->with('success', __('learning-tools::app.notes.updated'));
    }

    public function destroy(Request $request, LearningNote $note): RedirectResponse
    {
        $this->authorizeOwner($request, $note);
        $note->delete();

        return to_route('learning-tools.notes.index')->with('success', __('learning-tools::app.notes.deleted'));
    }

    private function authorizeOwner(Request $request, LearningNote $note): void
    {
        abort_unless((int) $note->owner_id === (int) $request->user()->getAuthIdentifier(), 403);
    }

    private function subjects()
    {
        return Subject::with(['topics' => fn ($query) => $query->where('status', 'active')])
            ->where('status', 'active')->orderBy('name')->get();
    }
}
