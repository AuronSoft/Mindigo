<?php

namespace Mindigo\TeacherLiveSession\Services;

use Mindigo\TeacherLiveSession\Contracts\LiveMeetingProvider;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Exceptions\UnsupportedLiveMeetingProvider;

final class LiveMeetingProviderRegistry
{
    /** @var array<string, LiveMeetingProvider> */
    private array $providers = [];

    public function __construct(private readonly LiveSessionConfigurationService $configuration) {}

    public function register(LiveMeetingProvider $provider): void
    {
        $this->providers[$provider->key()->value] = $provider;
    }

    public function resolve(LiveSessionProvider|string $provider): LiveMeetingProvider
    {
        $key = $provider instanceof LiveSessionProvider ? $provider->value : $provider;

        return $this->providers[$key] ?? throw UnsupportedLiveMeetingProvider::for($key);
    }

    /** @return array<string, ProviderCapabilities> */
    public function capabilities(): array
    {
        return collect($this->providers)
            ->filter(fn (LiveMeetingProvider $provider) => $this->configuration->providerEnabled($provider->key()))
            ->map(fn (LiveMeetingProvider $provider) => $provider->capabilities())
            ->all();
    }
}
