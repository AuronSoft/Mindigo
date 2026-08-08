<?php

namespace Mindigo\Core\Http\Controllers;

use App\Support\RoleRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mindigo\Core\Models\ExamTipPost;
use Mindigo\ExamManagement\Models\Exam;

class HomeController extends Controller
{
    public function index()
    {
        return view('core::home');
    }

    public function terms()
    {
        return view('core::legal.terms', [
            'title' => __('core::terms.hero.title').' | Mindigo',
        ]);
    }

    public function privacy()
    {
        return view('core::legal.privacy', [
            'title' => __('core::privacy.hero.title').' | Mindigo',
        ]);
    }

    public function technicalSupportPolicy()
    {
        return view('core::legal.technical-support', [
            'title' => __('core::technical_support.hero.title').' | Mindigo',
        ]);
    }

    public function aiAssistantPolicy()
    {
        return view('core::legal.ai-assistant-policy', [
            'title' => __('core::ai_policy.hero.title').' | Mindigo',
        ]);
    }

    public function refundPolicy()
    {
        return view('core::legal.refund-policy', [
            'title' => __('core::refund_policy.hero.title').' | Mindigo',
        ]);
    }

    public function tutorPolicy()
    {
        return view('core::legal.tutor-policy', [
            'title' => __('core::tutor_policy.hero.title').' | Mindigo',
        ]);
    }

    public function examTips(Request $request)
    {
        $categories = collect(__('core::exam_tips.categories'));
        $categoryLabels = $categories->keyBy('id')->map(fn (array $category) => $category['label']);
        $posts = ExamTipPost::query()
            ->with('user')
            ->published()
            ->latest('published_at')
            ->latest()
            ->get();

        return view('core::pages.exam-tips', [
            'title' => __('core::exam_tips.meta.title').' | Mindigo',
            'posts' => $posts->map(fn (ExamTipPost $post) => $this->formatExamTipPost($post, $categoryLabels)),
            'stats' => $this->examTipStats($posts),
            'trendingTags' => $this->examTipTrendingTags($posts),
            'contributors' => $this->examTipContributors($posts),
            'upcomingExams' => $this->examTipUpcomingExams(),
            'accountUrl' => RoleRedirector::pathFor($request->user()),
        ]);
    }

    public function storeExamTip(Request $request): RedirectResponse
    {
        $categories = collect(__('core::exam_tips.categories'))
            ->pluck('id')
            ->reject(fn (string $id) => $id === 'all')
            ->values()
            ->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'in:'.implode(',', $categories)],
            'content' => ['required', 'string', 'min:20', 'max:5000'],
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        $plainContent = trim(strip_tags($validated['content']));

        ExamTipPost::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'excerpt' => Str::limit($plainContent, 180),
            'content' => $validated['content'],
            'tags' => $this->parseExamTipTags($validated['tags'] ?? ''),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()
            ->route('exam-tips')
            ->with('exam_tip_shared', true);
    }

    private function formatExamTipPost(ExamTipPost $post, Collection $categoryLabels): array
    {
        $author = $post->user?->name ?: $post->user?->email ?: __('core::exam_tips.fallback.author');
        $plainContent = trim(strip_tags($post->content));

        return [
            'id' => $post->id,
            'category' => $post->category,
            'category_label' => $categoryLabels->get($post->category, $post->category),
            'title' => $post->title,
            'excerpt' => $post->excerpt ?: Str::limit($plainContent, 180),
            'author' => $author,
            'avatar' => $this->initials($author),
            'avatar_class' => $this->avatarClass((int) $post->user_id),
            'date' => ($post->published_at ?? $post->created_at)->diffForHumans(),
            'views' => $this->shortNumber((int) $post->views_count),
            'likes' => (int) $post->likes_count,
            'comments' => (int) $post->comments_count,
            'tags' => collect($post->tags ?? [])->filter()->values()->all(),
            'featured' => (bool) $post->is_featured,
            'read_time' => __('core::exam_tips.read_time', ['minutes' => max(1, (int) ceil(str_word_count($plainContent) / 220))]),
        ];
    }

    private function examTipStats(Collection $posts): array
    {
        return [
            ['label' => __('core::exam_tips.stats.posts'), 'value' => $this->shortNumber($posts->count())],
            ['label' => __('core::exam_tips.stats.members'), 'value' => $this->shortNumber($posts->pluck('user_id')->unique()->count())],
            ['label' => __('core::exam_tips.stats.reads'), 'value' => $this->shortNumber((int) $posts->sum('views_count'))],
        ];
    }

    private function examTipTrendingTags(Collection $posts): Collection
    {
        return $posts
            ->flatMap(fn (ExamTipPost $post) => $post->tags ?? [])
            ->filter()
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(9)
            ->values();
    }

    private function examTipContributors(Collection $posts): Collection
    {
        return $posts
            ->groupBy('user_id')
            ->map(function (Collection $userPosts) {
                $post = $userPosts->first();
                $name = $post->user?->name ?: $post->user?->email ?: __('core::exam_tips.fallback.author');

                return [
                    'name' => $name,
                    'posts' => $userPosts->count(),
                    'likes' => $this->shortNumber((int) $userPosts->sum('likes_count')),
                    'avatar' => $this->initials($name),
                    'class' => $this->avatarClass((int) $post->user_id),
                ];
            })
            ->sortByDesc(fn (array $user) => $user['posts'])
            ->take(4)
            ->values();
    }

    private function examTipUpcomingExams(): Collection
    {
        if (! class_exists(Exam::class)) {
            return collect();
        }

        return Exam::query()
            ->where('status', 'published')
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(3)
            ->get()
            ->map(fn (Exam $exam, int $index) => [
                'name' => $exam->title,
                'date' => $exam->starts_at->translatedFormat('d/m/Y'),
                'days' => now()->diffInDays($exam->starts_at),
                'class' => ['bg-blue-500', 'bg-emerald-500', 'bg-violet-500'][$index % 3],
            ]);
    }

    private function parseExamTipTags(?string $tags): array
    {
        return collect(explode(',', (string) $tags))
            ->map(fn (string $tag) => Str::of($tag)->trim()->trim('#')->toString())
            ->filter()
            ->unique(fn (string $tag) => Str::lower($tag))
            ->take(6)
            ->values()
            ->all();
    }

    private function shortNumber(int $value): string
    {
        if ($value >= 1000000) {
            return round($value / 1000000, 1).'M';
        }

        if ($value >= 1000) {
            return round($value / 1000, 1).'K';
        }

        return (string) $value;
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->replaceMatches('/[^\pL\pN\s]+/u', '')
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::of($part)->substr(0, 1)->upper())
            ->implode('');
    }

    private function avatarClass(int $seed): string
    {
        $classes = ['bg-blue-500', 'bg-emerald-500', 'bg-rose-500', 'bg-violet-500', 'bg-amber-500', 'bg-orange-500', 'bg-cyan-500'];

        return $classes[$seed % count($classes)];
    }
}
