<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuestSignal;
use Mindigo\TeacherLiveSession\Models\LiveSessionRoomEvent;
use Mindigo\TeacherLiveSession\Models\LiveSessionSignal;

final class CleanupLiveRealtimeCommand extends Command
{
    protected $signature = 'live-sessions:cleanup-realtime';

    protected $description = 'Remove expired transient signalling and room event data';

    public function handle(): int
    {
        $signals = LiveSessionSignal::query()
            ->where(fn ($query) => $query->where('created_at', '<', now()->subHour())
                ->orWhere(fn ($consumed) => $consumed->whereNotNull('consumed_at')->where('consumed_at', '<', now()->subMinutes(15))))
            ->delete();
        $guestSignals = LiveSessionGuestSignal::query()
            ->where(fn ($query) => $query->where('created_at', '<', now()->subHour())
                ->orWhere(fn ($consumed) => $consumed->whereNotNull('consumed_at')->where('consumed_at', '<', now()->subMinutes(15))))
            ->delete();
        $events = LiveSessionRoomEvent::query()->where('expires_at', '<', now())->delete();

        $this->info("Realtime cleanup completed: {$signals} signals, {$guestSignals} guest signals, {$events} events.");

        return self::SUCCESS;
    }
}
