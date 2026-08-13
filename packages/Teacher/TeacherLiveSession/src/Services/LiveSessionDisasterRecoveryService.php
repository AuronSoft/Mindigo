<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use Mindigo\TeacherLiveSession\Models\LiveSessionResource;
use RuntimeException;
use ZipArchive;

final class LiveSessionDisasterRecoveryService
{
    private const MAGIC = 'MINDIGO-DR-1';

    public function backup(?string $disk = null): array
    {
        $disk ??= (string) config('live-disaster-recovery.disk', 'local');
        throw_if(app()->environment('production') && $disk === 'local' && config('live-disaster-recovery.require_offsite_in_production'), RuntimeException::class, 'Production disaster-recovery archives require an off-site filesystem disk.');
        $workspace = $this->workspace();

        try {
            $manifest = ['version' => 1, 'created_at' => now()->toIso8601String(), 'database' => [], 'files' => []];
            $zipPath = $workspace.'/archive.zip';
            $zip = new ZipArchive;
            throw_unless($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, RuntimeException::class, 'Unable to create disaster-recovery archive.');

            DB::transaction(function () use ($workspace, $zip, &$manifest): void {
                foreach ($this->tables() as $table) {
                    $entry = 'database/'.rawurlencode($table).'.jsonl';
                    $temporary = $workspace.'/'.hash('sha256', $table).'.jsonl';
                    $handle = fopen($temporary, 'wb');
                    throw_unless(is_resource($handle), RuntimeException::class, "Unable to export table {$table}.");
                    $count = 0;
                    foreach (DB::table($table)->orderBy($this->orderColumn($table))->cursor() as $row) {
                        fwrite($handle, json_encode((array) $row, JSON_THROW_ON_ERROR)."\n");
                        $count++;
                    }
                    fclose($handle);
                    $zip->addFile($temporary, $entry);
                    $manifest['database'][$table] = ['entry' => $entry, 'records' => $count, 'sha256' => hash_file('sha256', $temporary)];
                }
            });

            if ((bool) config('live-disaster-recovery.include_media', true)) {
                foreach ($this->mediaFiles() as $file) {
                    $source = Storage::disk($file['disk'])->readStream($file['path']);
                    if (! is_resource($source)) {
                        continue;
                    }
                    $temporary = $workspace.'/media-'.hash('sha256', $file['disk'].'|'.$file['path']);
                    $target = fopen($temporary, 'wb');
                    stream_copy_to_stream($source, $target);
                    fclose($source);
                    fclose($target);
                    $entry = 'files/'.rawurlencode($file['disk']).'/'.ltrim($file['path'], '/');
                    $zip->addFile($temporary, $entry);
                    $manifest['files'][] = [...$file, 'entry' => $entry, 'size' => filesize($temporary), 'sha256' => hash_file('sha256', $temporary)];
                }
            }

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            $zip->close();
            $encrypted = $workspace.'/archive.mdr';
            $this->encrypt($zipPath, $encrypted);
            $path = $this->prefix().'/mindigo-dr-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.mdr';
            $stream = fopen($encrypted, 'rb');
            throw_unless(Storage::disk($disk)->writeStream($path, $stream), RuntimeException::class, 'Unable to write off-site disaster-recovery archive.');
            fclose($stream);
            $verified = ! config('live-disaster-recovery.verify_after_backup') || $this->drill($path, $disk)['tables'] === count($manifest['database']);
            throw_unless($verified, RuntimeException::class, 'Post-backup disaster-recovery verification failed.');
            $this->rotate($disk);

            return ['path' => $path, 'disk' => $disk, 'verified' => $verified, 'size' => Storage::disk($disk)->size($path), 'tables' => count($manifest['database']), 'records' => collect($manifest['database'])->sum('records'), 'files' => count($manifest['files']), 'sha256' => hash_file('sha256', $encrypted)];
        } finally {
            $this->removeWorkspace($workspace);
        }
    }

    public function drill(string $path, ?string $disk = null): array
    {
        $disk ??= (string) config('live-disaster-recovery.disk', 'local');
        $workspace = $this->workspace();

        try {
            $encrypted = $workspace.'/archive.mdr';
            $stream = Storage::disk($disk)->readStream($path);
            throw_unless(is_resource($stream), RuntimeException::class, 'Disaster-recovery archive is unavailable.');
            $target = fopen($encrypted, 'wb');
            stream_copy_to_stream($stream, $target);
            fclose($stream);
            fclose($target);
            $zipPath = $workspace.'/archive.zip';
            $this->decrypt($encrypted, $zipPath);

            return $this->validateZip($zipPath);
        } finally {
            $this->removeWorkspace($workspace);
        }
    }

    public function restore(string $path, ?string $disk = null): array
    {
        $disk ??= (string) config('live-disaster-recovery.disk', 'local');
        $workspace = $this->workspace();

        try {
            $encrypted = $workspace.'/archive.mdr';
            $input = Storage::disk($disk)->readStream($path);
            throw_unless(is_resource($input), RuntimeException::class, 'Disaster-recovery archive is unavailable.');
            $output = fopen($encrypted, 'wb');
            stream_copy_to_stream($input, $output);
            fclose($input);
            fclose($output);
            $zipPath = $workspace.'/archive.zip';
            $this->decrypt($encrypted, $zipPath);
            $inspection = $this->validateZip($zipPath);
            $zip = new ZipArchive;
            $zip->open($zipPath);
            Schema::disableForeignKeyConstraints();
            try {
                DB::transaction(function () use ($zip, $inspection): void {
                    foreach ($inspection['manifest']['database'] as $table => $metadata) {
                        if (! Schema::hasTable($table)) {
                            continue;
                        }
                        $stream = $zip->getStream($metadata['entry']);
                        throw_unless(is_resource($stream), RuntimeException::class, 'Database entry is unavailable during restore.');
                        $batch = [];
                        while (($row = fgets($stream)) !== false) {
                            $batch[] = json_decode($row, true, flags: JSON_THROW_ON_ERROR);
                            if (count($batch) === 250) {
                                DB::table($table)->insertOrIgnore($batch);
                                $batch = [];
                            }
                        }
                        if ($batch !== []) {
                            DB::table($table)->insertOrIgnore($batch);
                        }
                        fclose($stream);
                    }
                });
            } finally {
                Schema::enableForeignKeyConstraints();
            }
            foreach ($inspection['manifest']['files'] as $file) {
                $stream = $zip->getStream($file['entry']);
                throw_unless(is_resource($stream), RuntimeException::class, 'Media entry is unavailable during restore.');
                throw_unless(Storage::disk($file['disk'])->writeStream($file['path'], $stream), RuntimeException::class, 'Unable to restore media entry.');
                fclose($stream);
            }
            $zip->close();

            return ['tables' => count($inspection['manifest']['database']), 'records' => collect($inspection['manifest']['database'])->sum('records'), 'files' => count($inspection['manifest']['files'])];
        } finally {
            $this->removeWorkspace($workspace);
        }
    }

    private function validateZip(string $path): array
    {
        $zip = new ZipArchive;
        throw_unless($zip->open($path) === true, RuntimeException::class, 'Decrypted archive is not a valid ZIP file.');
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, flags: JSON_THROW_ON_ERROR);
        throw_unless(($manifest['version'] ?? null) === 1, RuntimeException::class, 'Unsupported disaster-recovery archive version.');
        foreach ([...array_values($manifest['database']), ...$manifest['files']] as $metadata) {
            $stream = $zip->getStream($metadata['entry']);
            throw_unless(is_resource($stream), RuntimeException::class, 'Disaster-recovery archive entry is unavailable.');
            $hash = hash_init('sha256');
            hash_update_stream($hash, $stream);
            fclose($stream);
            throw_unless(hash_equals($metadata['sha256'], hash_final($hash)), RuntimeException::class, 'Disaster-recovery archive checksum mismatch.');
        }
        $zip->close();

        return ['manifest' => $manifest, 'tables' => count($manifest['database']), 'records' => collect($manifest['database'])->sum('records'), 'files' => count($manifest['files'])];
    }

    private function encrypt(string $source, string $target): void
    {
        $key = $this->key();
        [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);
        $input = fopen($source, 'rb');
        $output = fopen($target, 'wb');
        fwrite($output, self::MAGIC.$header);
        while (! feof($input)) {
            $chunk = fread($input, 1024 * 1024);
            $final = feof($input);
            $cipher = sodium_crypto_secretstream_xchacha20poly1305_push($state, $chunk, '', $final ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL : 0);
            fwrite($output, pack('N', strlen($cipher)).$cipher);
        }
        fclose($input);
        fclose($output);
    }

    private function decrypt(string $source, string $target): void
    {
        $input = fopen($source, 'rb');
        throw_unless(fread($input, strlen(self::MAGIC)) === self::MAGIC, RuntimeException::class, 'Invalid disaster-recovery archive signature.');
        $header = fread($input, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES);
        $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $this->key());
        $output = fopen($target, 'wb');
        $finalized = false;
        while (($lengthBytes = fread($input, 4)) !== '') {
            throw_unless(strlen($lengthBytes) === 4, RuntimeException::class, 'Truncated disaster-recovery archive.');
            $length = unpack('Nlength', $lengthBytes)['length'];
            $cipher = '';
            while (strlen($cipher) < $length && ! feof($input)) {
                $cipher .= fread($input, $length - strlen($cipher));
            }
            if ($cipher === '') {
                break;
            }
            throw_unless(strlen($cipher) === $length, RuntimeException::class, 'Truncated disaster-recovery archive.');
            $result = sodium_crypto_secretstream_xchacha20poly1305_pull($state, $cipher);
            throw_unless($result !== false, RuntimeException::class, 'Unable to authenticate disaster-recovery archive.');
            fwrite($output, $result[0]);
            $finalized = $result[1] === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL;
        }
        fclose($input);
        fclose($output);
        throw_unless($finalized, RuntimeException::class, 'Disaster-recovery archive is incomplete.');
    }

    private function key(): string
    {
        throw_unless(extension_loaded('sodium'), RuntimeException::class, 'The Sodium PHP extension is required for disaster-recovery encryption.');
        $encoded = (string) config('live-disaster-recovery.encryption_key');
        $key = base64_decode($encoded, true);
        throw_unless(is_string($key) && strlen($key) === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES, RuntimeException::class, 'LIVE_DR_ENCRYPTION_KEY must be a base64-encoded 32-byte key.');

        return $key;
    }

    private function tables(): array
    {
        return collect(Schema::getTables())->pluck('name')->sort()->values()->all();
    }

    private function orderColumn(string $table): string
    {
        $columns = Schema::getColumnListing($table);

        return in_array('id', $columns, true) ? 'id' : $columns[0];
    }

    private function mediaFiles(): array
    {
        $recordings = LiveSessionRecording::query()->where('status', 'ready')->get()->flatMap(function (LiveSessionRecording $recording): array {
            $disk = Storage::disk($recording->storage_disk);
            $paths = collect([$recording->storage_path, $recording->source_path, $recording->hls_manifest_path])->filter();
            foreach ($paths->filter(fn (string $path): bool => str_ends_with($path, '.json') || str_ends_with($path, '.m3u8')) as $path) {
                $paths = $paths->merge($disk->allFiles(dirname($path)));
            }

            return $paths->map(fn (string $path): array => ['disk' => $recording->storage_disk, 'path' => $path])->all();
        });
        $resources = LiveSessionResource::query()->get()->map(fn (LiveSessionResource $resource): array => ['disk' => $resource->storage_disk, 'path' => $resource->storage_path]);

        return $recordings->merge($resources)->unique(fn (array $file): string => $file['disk'].'|'.$file['path'])->filter(fn (array $file): bool => Storage::disk($file['disk'])->exists($file['path']))->values()->all();
    }

    private function rotate(string $disk): void
    {
        $keep = max(1, (int) config('live-disaster-recovery.retention_copies', 14));
        $files = collect(Storage::disk($disk)->files($this->prefix()))->filter(fn (string $file): bool => str_ends_with($file, '.mdr'))->sortDesc()->values();
        Storage::disk($disk)->delete($files->slice($keep)->all());
    }

    private function prefix(): string
    {
        return trim((string) config('live-disaster-recovery.prefix', 'disaster-recovery/live-sessions'), '/');
    }

    private function workspace(): string
    {
        $path = storage_path('framework/cache/live-dr-'.bin2hex(random_bytes(8)));
        mkdir($path, 0700, true);

        return $path;
    }

    private function removeWorkspace(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }
        foreach (glob($path.'/*') ?: [] as $file) {
            is_file($file) && @unlink($file);
        }
        @rmdir($path);
    }
}
