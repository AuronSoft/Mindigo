<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherLiveSession\Models\LiveSessionGuestSignal;
use Mindigo\TeacherLiveSession\Models\LiveSessionMessage;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Models\LiveSessionRoomEvent;
use Mindigo\TeacherLiveSession\Models\LiveSessionSignal;
use Mindigo\TeacherLiveSession\Services\LiveSessionConfigurationService;

final class PruneLiveSessionDataCommand extends Command
{
    protected $signature = 'live-sessions:prune-data {--dry-run : Report eligible records without deleting them}';

    protected $description = 'Apply configured retention rules to live-session data and recordings';

    public function handle(LiveSessionConfigurationService $configuration): int
    {
        $dataCutoff = now()->subDays((int) $configuration->value('live_data_retention_days'));
        $recordingCutoff = now()->subDays((int) $configuration->value('live_recording_retention_days'));
        $queries = [
            'messages' => LiveSessionMessage::query()->where('created_at', '<', $dataCutoff),
            'room events' => LiveSessionRoomEvent::query()->where('created_at', '<', $dataCutoff),
            'signals' => LiveSessionSignal::query()->where('created_at', '<', $dataCutoff),
            'guest signals' => LiveSessionGuestSignal::query()->where('created_at', '<', $dataCutoff),
        ];

        $counts = collect($queries)->map(fn ($query): int => (clone $query)->count());
        $recordings = LiveSessionRecording::query()
            ->whereNotNull('ended_at')
            ->where('ended_at', '<', $recordingCutoff)
            ->get();

        if (! $this->option('dry-run')) {
            foreach ($queries as $query) {
                $query->delete();
            }
            foreach ($recordings as $recording) {
                if ($recording->storage_path) {
                    Storage::disk($recording->storage_disk)->delete($recording->storage_path);
                }
                Storage::disk($recording->storage_disk)->delete($recording->chunks()->pluck('storage_path')->all());
                $recording->delete();
            }
        }

        $mode = $this->option('dry-run') ? 'Dry run' : 'Pruning';
        $this->info("{$mode} completed: {$counts->sum()} data records, {$recordings->count()} recordings.");

        return self::SUCCESS;
    }
}
