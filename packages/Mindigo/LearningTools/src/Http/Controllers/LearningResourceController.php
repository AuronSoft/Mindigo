<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mindigo\LearningTools\Http\Requests\LearningResourceRequest;
use Mindigo\LearningTools\Models\LearningResource;
use Mindigo\SubjectManagement\Models\Subject;

class LearningResourceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = LearningResource::with(['subject', 'topic', 'author'])->withCount('favoritedBy');

        if ($user->role === 'student') {
            $query->where('status', 'published');
        } else {
            $query->where(fn ($builder) => $builder->where('status', 'published')->orWhere('author_id', $user->getAuthIdentifier()));
        }
        if ($request->filled('q')) {
            $query->where(fn ($builder) => $builder->where('title', 'like', '%'.$request->string('q').'%')
                ->orWhere('summary', 'like', '%'.$request->string('q').'%'));
        }
        if ($request->filled('subject')) {
            $query->where('subject_id', $request->integer('subject'));
        }

        return view('learning-tools::resources.index', [
            'resources' => $query->latest('published_at')->latest()->paginate(12)->withQueryString(),
            'subjects' => $this->subjects(),
        ]);
    }

    public function show(Request $request, LearningResource $resource): View
    {
        $this->authorizeView($request, $resource);
        $resource->load(['subject', 'topic', 'author']);
        $isFavorite = $resource->favoritedBy()->whereKey($request->user()->getAuthIdentifier())->exists();

        return view('learning-tools::resources.show', compact('resource', 'isFavorite'));
    }

    public function create(): View
    {
        return view('learning-tools::resources.form', ['resource' => new LearningResource, 'subjects' => $this->subjects()]);
    }

    public function store(LearningResourceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $resource = LearningResource::create([
            ...$data,
            'author_id' => $request->user()->getAuthIdentifier(),
            'slug' => $this->uniqueSlug($data['title']),
            'published_at' => $data['status'] === 'published' ? now() : null,
        ]);

        return to_route('learning-tools.resources.show', $resource)->with('success', __('learning-tools::app.resources.created'));
    }

    public function edit(Request $request, LearningResource $resource): View
    {
        $this->authorizeOwner($request, $resource);

        return view('learning-tools::resources.form', compact('resource') + ['subjects' => $this->subjects()]);
    }

    public function update(LearningResourceRequest $request, LearningResource $resource): RedirectResponse
    {
        $this->authorizeOwner($request, $resource);
        $data = $request->validated();
        $resource->update([
            ...$data,
            'published_at' => $data['status'] === 'published' ? ($resource->published_at ?? now()) : null,
        ]);

        return to_route('learning-tools.resources.show', $resource)->with('success', __('learning-tools::app.resources.updated'));
    }

    public function destroy(Request $request, LearningResource $resource): RedirectResponse
    {
        $this->authorizeOwner($request, $resource);
        $resource->delete();

        return to_route('learning-tools.resources.index')->with('success', __('learning-tools::app.resources.deleted'));
    }

    public function favorite(Request $request, LearningResource $resource): RedirectResponse
    {
        $this->authorizeView($request, $resource);
        $resource->favoritedBy()->toggle($request->user()->getAuthIdentifier());

        return back();
    }

    private function authorizeView(Request $request, LearningResource $resource): void
    {
        abort_unless($resource->status === 'published' || (int) $resource->author_id === (int) $request->user()->getAuthIdentifier(), 403);
    }

    private function authorizeOwner(Request $request, LearningResource $resource): void
    {
        abort_unless($request->user()->role === 'admin' || (int) $resource->author_id === (int) $request->user()->getAuthIdentifier(), 403);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::random(8);
        $slug = $base;
        $suffix = 2;
        while (LearningResource::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function subjects()
    {
        return Subject::with(['topics' => fn ($query) => $query->where('status', 'active')])
            ->where('status', 'active')->orderBy('name')->get();
    }
}
