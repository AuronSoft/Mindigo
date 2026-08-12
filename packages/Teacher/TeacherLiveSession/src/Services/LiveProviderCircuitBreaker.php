<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Contracts\Cache\Repository;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Exceptions\ProviderCircuitOpenException;

final class LiveProviderCircuitBreaker
{
    public function __construct(private readonly Repository $cache) {}

    public function assertAvailable(LiveSessionProvider $provider): void
    {
        if ($provider === LiveSessionProvider::Native) {
            return;
        }

        $state = $this->state($provider);
        if ($state['open_until'] !== null && $state['open_until'] > now()->timestamp) {
            throw new ProviderCircuitOpenException("Provider {$provider->value} is temporarily unavailable.");
        }

        if ($state['open_until'] !== null) {
            $this->reset($provider);
        }
    }

    public function recordSuccess(LiveSessionProvider $provider): void
    {
        $this->reset($provider);
    }

    public function recordFailure(LiveSessionProvider $provider): void
    {
        if ($provider === LiveSessionProvider::Native) {
            return;
        }

        $state = $this->state($provider);
        $failures = $state['failures'] + 1;
        $threshold = max(1, (int) config('live-providers.circuit_breaker.failure_threshold', 3));
        $cooldown = max(30, (int) config('live-providers.circuit_breaker.cooldown_seconds', 300));

        $this->cache->put($this->key($provider), [
            'failures' => $failures,
            'open_until' => $failures >= $threshold ? now()->addSeconds($cooldown)->timestamp : null,
            'last_failure_at' => now()->toIso8601String(),
        ], $cooldown * 2);
    }

    public function state(LiveSessionProvider $provider): array
    {
        if ($provider === LiveSessionProvider::Native) {
            return ['failures' => 0, 'open_until' => null, 'last_failure_at' => null, 'available' => true];
        }

        $state = $this->cache->get($this->key($provider), []);
        $openUntil = $state['open_until'] ?? null;

        return [
            'failures' => (int) ($state['failures'] ?? 0),
            'open_until' => $openUntil,
            'last_failure_at' => $state['last_failure_at'] ?? null,
            'available' => $openUntil === null || $openUntil <= now()->timestamp,
        ];
    }

    public function reset(LiveSessionProvider $provider): void
    {
        $this->cache->forget($this->key($provider));
    }

    private function key(LiveSessionProvider $provider): string
    {
        return "live-provider:circuit:{$provider->value}";
    }
}
