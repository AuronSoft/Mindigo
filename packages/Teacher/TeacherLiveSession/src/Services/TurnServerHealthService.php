<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class TurnServerHealthService
{
    public const CACHE_KEY = 'live-media:turn-health';

    public function refresh(): array
    {
        $states = collect(config('live-media.turn.nodes', []))->mapWithKeys(function (array $node): array {
            $healthUrl = $node['health_url'] ?? null;
            if (blank($healthUrl)) {
                return [$node['id'] => ['healthy' => true, 'checked_at' => now()->toIso8601String(), 'reason' => 'not_probed']];
            }

            try {
                $response = Http::acceptJson()->connectTimeout(1)->timeout(2)->get($healthUrl);
                $healthy = $response->successful();

                return [$node['id'] => ['healthy' => $healthy, 'checked_at' => now()->toIso8601String(), 'reason' => $healthy ? null : 'http_'.$response->status()]];
            } catch (Throwable $exception) {
                return [$node['id'] => ['healthy' => false, 'checked_at' => now()->toIso8601String(), 'reason' => class_basename($exception)]];
            }
        })->all();

        Cache::put(self::CACHE_KEY, $states, max(30, (int) config('live-media.turn.health_cache_seconds', 90) * 2));

        return $states;
    }

    public function availableNodes(): array
    {
        $nodes = config('live-media.turn.nodes', []);
        if ($nodes === []) {
            return [];
        }

        $states = Cache::get(self::CACHE_KEY);
        if (! is_array($states)) {
            $states = $this->refresh();
        }
        $healthy = array_values(array_filter($nodes, fn (array $node): bool => ($states[$node['id']]['healthy'] ?? false) === true));

        return $healthy !== [] || ! config('live-media.turn.fail_open', true) ? $healthy : $nodes;
    }

    public function states(): array
    {
        return Cache::get(self::CACHE_KEY, []);
    }
}
