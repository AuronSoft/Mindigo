<?php

namespace Mindigo\LearningTools\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LearningToolsController extends Controller
{
    public function index(Request $request): View
    {
        $role = (string) $request->user()->role;
        $search = trim((string) $request->query('q'));
        $category = (string) $request->query('category', 'all');
        $categories = collect(config('learning-tools.categories'));

        if (! $categories->has($category)) {
            $category = 'all';
        }

        $tools = collect(config('learning-tools.tools'))
            ->filter(fn (array $tool): bool => in_array($role, $tool['roles'], true))
            ->map(fn (array $tool, string $key): array => [
                ...$tool,
                'key' => $key,
                'name' => __("learning-tools::app.tools.{$key}.name"),
                'description' => __("learning-tools::app.tools.{$key}.description"),
            ])
            ->when($category !== 'all', fn (Collection $items): Collection => $items->where('category', $category))
            ->when($search !== '', function (Collection $items) use ($search): Collection {
                return $items->filter(fn (array $tool): bool => str_contains(
                    mb_strtolower($tool['name'].' '.$tool['description']),
                    mb_strtolower($search)
                ));
            })
            ->values();

        return view('learning-tools::index', compact('tools', 'categories', 'category', 'search'));
    }
}
