<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class LiveSessionArchiveService
{
    private const TABLES = [
        'live_sessions', 'live_session_guest_links', 'live_session_guests', 'live_session_participants',
        'live_session_attendances', 'live_session_attendance_intervals', 'live_session_messages',
        'live_session_room_events', 'live_session_recordings', 'live_session_resources',
    ];

    private const REDACTED_COLUMNS = ['provider_host_url', 'sync_error', 'token_hash', 'access_token_hash', 'ip_hash'];

    public function backup(string $disk = 'local'): array
    {
        $tables = collect(self::TABLES)->filter(fn (string $table): bool => Schema::hasTable($table))
            ->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->orderBy('id')->get()
                ->map(fn (object $row): array => $this->redactRow((array) $row))->all()])->all();
        $payload = json_encode(['version' => 1, 'created_at' => now()->toIso8601String(), 'tables' => $tables], JSON_THROW_ON_ERROR);
        $checksum = hash('sha256', $payload);
        $path = 'live-session-backups/live-sessions-'.now()->format('Ymd-His').'.json.gz';
        Storage::disk($disk)->put($path, gzencode($payload, 9));

        return ['path' => $path, 'checksum' => $checksum, 'records' => collect($tables)->sum(fn (array $rows): int => count($rows))];
    }

    public function inspect(string $path, string $disk = 'local'): array
    {
        abort_unless(Storage::disk($disk)->exists($path), 404);
        $payload = gzdecode(Storage::disk($disk)->get($path));
        if ($payload === false) {
            throw new RuntimeException('Invalid live-session backup archive.');
        }
        $archive = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        if (($archive['version'] ?? null) !== 1 || ! is_array($archive['tables'] ?? null)) {
            throw new RuntimeException('Unsupported live-session backup version.');
        }

        return ['archive' => $archive, 'checksum' => hash('sha256', $payload)];
    }

    public function restore(string $path, string $disk = 'local'): int
    {
        $archive = $this->inspect($path, $disk)['archive'];

        return DB::transaction(function () use ($archive): int {
            $restored = 0;
            foreach (self::TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                foreach ($archive['tables'][$table] ?? [] as $row) {
                    $restored += DB::table($table)->insertOrIgnore(collect($row)->only(Schema::getColumnListing($table))->all());
                }
            }

            return $restored;
        });
    }

    private function redactRow(array $row): array
    {
        $row = collect($row)->except(self::REDACTED_COLUMNS)->all();
        if (! isset($row['provider_metadata'])) {
            return $row;
        }

        $metadata = is_string($row['provider_metadata'])
            ? json_decode($row['provider_metadata'], true)
            : $row['provider_metadata'];
        if (! is_array($metadata)) {
            unset($row['provider_metadata']);

            return $row;
        }

        $sensitiveKeys = ['password', 'passcode', 'secret', 'token', 'access_token', 'refresh_token', 'client_secret', 'start_url', 'zak'];
        $sanitize = function (array $values) use (&$sanitize, $sensitiveKeys): array {
            foreach ($values as $key => $value) {
                if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                    unset($values[$key]);
                } elseif (is_array($value)) {
                    $values[$key] = $sanitize($value);
                }
            }

            return $values;
        };
        $row['provider_metadata'] = json_encode($sanitize($metadata), JSON_THROW_ON_ERROR);

        return $row;
    }
}
