<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Services\TurnServerHealthService;

final class CheckTurnHealthCommand extends Command
{
    protected $signature = 'live-sessions:check-turn';

    protected $description = 'Probe configured TURN nodes and refresh the failover registry';

    public function handle(TurnServerHealthService $health): int
    {
        $states = $health->refresh();
        if ($states === []) {
            $this->warn('No TURN nodes are configured.');

            return self::FAILURE;
        }

        $this->table(['Node', 'Status', 'Checked at', 'Reason'], collect($states)->map(
            fn (array $state, string $id): array => [$id, $state['healthy'] ? 'healthy' : 'unavailable', $state['checked_at'], $state['reason'] ?? '']
        )->values()->all());

        return collect($states)->contains(fn (array $state): bool => $state['healthy']) ? self::SUCCESS : self::FAILURE;
    }
}
