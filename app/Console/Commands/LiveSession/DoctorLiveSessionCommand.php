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

        $hasStunServer = collect(config('live-media.static_ice_servers', []))->contains(fn ($server) => filled($server['urls'] ?? null));
        $turnNodes = config('live-media.turn.nodes', []);
        $turnConfigured = $turnNodes !== [] && mb_strlen((string) config('live-media.turn.auth_secret')) >= 32;
        $hasIceServer = $hasStunServer || $turnConfigured;
        $this->components->twoColumnDetail('WebRTC ICE servers', $hasIceServer ? '<fg=green>configured</>' : '<fg=yellow>missing</>');
        $this->components->twoColumnDetail('Dynamic TURN credentials', $turnConfigured ? '<fg=green>configured</>' : '<fg=yellow>missing</>');
        $this->components->twoColumnDetail('Scheduler', '<fg=green>commands registered</>');
        $googleWebhookConfigured = filled(config('live-providers.google_meet.webhook_token'))
            && str_starts_with((string) config('live-providers.google_meet.calendar_webhook_url'), 'https://');
        $zoomWebhookConfigured = filled(config('live-providers.zoom.webhook_secret'))
            && str_starts_with((string) config('live-providers.zoom.webhook_url'), 'https://');
        $this->components->twoColumnDetail('Google provider webhooks', $googleWebhookConfigured ? '<fg=green>configured</>' : '<fg=yellow>missing or non-HTTPS</>');
        $this->components->twoColumnDetail('Zoom provider webhooks', $zoomWebhookConfigured ? '<fg=green>configured</>' : '<fg=yellow>missing or non-HTTPS</>');
        $drKey = base64_decode((string) config('live-disaster-recovery.encryption_key'), true);
        $drKeyConfigured = extension_loaded('sodium') && is_string($drKey) && strlen($drKey) === 32;
        $drDisk = (string) config('live-disaster-recovery.disk', 'local');
        $drOffsite = $drDisk !== 'local';
        $this->components->twoColumnDetail('Encrypted disaster recovery', $drKeyConfigured ? '<fg=green>configured</>' : '<fg=yellow>missing key</>');
        $this->components->twoColumnDetail('Off-site recovery disk', $drOffsite ? "<fg=green>{$drDisk}</>" : '<fg=yellow>local only</>');
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

        if (app()->isProduction() && ! $turnConfigured) {
            $this->error('Production Native media requires at least one TURN node and an auth secret of at least 32 characters.');

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

        if (app()->isProduction() && (! $drKeyConfigured || (config('live-disaster-recovery.require_offsite_in_production') && ! $drOffsite))) {
            $this->error('Production requires a dedicated 32-byte disaster-recovery key and an off-site filesystem disk.');

            return self::FAILURE;
        }

        if (app()->isProduction() && ($providers->resolve(LiveSessionProvider::GoogleMeet)->health()->available && ! $googleWebhookConfigured)) {
            $this->error('Google Meet is enabled but its signed HTTPS webhook channel is incomplete.');

            return self::FAILURE;
        }

        if (app()->isProduction() && ($providers->resolve(LiveSessionProvider::Zoom)->health()->available && ! $zoomWebhookConfigured)) {
            $this->error('Zoom is enabled but its signed HTTPS webhook endpoint is incomplete.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
