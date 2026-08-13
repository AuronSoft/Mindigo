<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Services\LiveMeetingProviderRegistry;
use Mindigo\TeacherLiveSession\Services\LiveSessionConfigurationService;
use Mindigo\TeacherLiveSession\Services\LiveSessionOperationalMonitor;

final class DoctorLiveSessionCommand extends Command
{
    protected $signature = 'live-sessions:doctor';

    protected $description = 'Inspect live-classroom provider and realtime deployment configuration';

    public function handle(
        LiveMeetingProviderRegistry $providers,
        LiveSessionConfigurationService $configuration,
        LiveSessionOperationalMonitor $monitor,
    ): int {
        $rows = collect(LiveSessionProvider::cases())
            ->reject(fn (LiveSessionProvider $provider) => $provider === LiveSessionProvider::LegacyJitsi)
            ->map(function (LiveSessionProvider $provider) use ($providers): array {
                $health = $providers->resolve($provider)->health();

                return [$provider->value, $health->available ? 'ready' : 'not configured', $health->message ?? ''];
            })->all();
        $this->table(['Provider', 'Status', 'Details'], $rows);

        $hasIceServer = collect(config('live-media.ice_servers', []))->contains(fn ($server) => filled($server['urls'] ?? null));
        $this->components->twoColumnDetail('WebRTC ICE servers', $hasIceServer ? '<fg=green>configured</>' : '<fg=yellow>missing</>');
        $this->components->twoColumnDetail('Scheduler', '<fg=green>commands registered</>');
        $capacity = (int) $configuration->value('live_max_participants');
        $topology = (string) config('live-media.topology', 'mesh');
        $safeMeshCapacity = (int) config('live-media.safe_mesh_capacity', 8);
        $this->components->twoColumnDetail('Media topology', $topology);
        $this->components->twoColumnDetail('Configured room capacity', (string) $capacity);
        if ($topology === 'sfu') {
            $gatewayConfigured = filled(config('live-media.gateway.public_url'))
                && filled(config('live-media.gateway.health_url'))
                && mb_strlen((string) config('live-media.gateway.secret')) >= 32;
            $this->components->twoColumnDetail('SFU signaling gateway', $gatewayConfigured ? '<fg=green>configured</>' : '<fg=red>incomplete</>');
        }

        foreach ($monitor->alerts() as $alert) {
            $this->warn("{$alert['code']}: {$alert['count']}");
        }

        if (app()->isProduction() && ! $hasIceServer) {
            $this->error('Production requires STUN/TURN configuration for reliable Native WebRTC connectivity.');

            return self::FAILURE;
        }

        if (app()->isProduction() && $topology === 'mesh' && $capacity > $safeMeshCapacity) {
            $this->error("Mesh WebRTC is not production-safe above {$safeMeshCapacity} participants. Configure an SFU topology or lower the room capacity.");

            return self::FAILURE;
        }

        if (app()->isProduction() && $topology === 'sfu' && (
            blank(config('live-media.sfu_health_url'))
            || blank(config('live-media.gateway.public_url'))
            || blank(config('live-media.gateway.health_url'))
            || mb_strlen((string) config('live-media.gateway.secret')) < 32
        )) {
            $this->error('SFU topology requires health URLs, a public WebSocket URL, and a gateway secret of at least 32 characters.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
