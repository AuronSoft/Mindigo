<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\Storage;
use Mindigo\TeacherLiveSession\Models\LiveSessionRecording;
use RuntimeException;
use Symfony\Component\Process\Process;

final class LiveServerRecordingProcessor
{
    public function process(LiveSessionRecording $recording): void
    {
        $disk = Storage::disk($recording->storage_disk);
        if (! $recording->source_path || ! $disk->exists($recording->source_path)) {
            throw new RuntimeException('Server recording source is unavailable.');
        }

        $recording->increment('processing_attempts');
        $recording->update(['progress' => 10, 'processing_started_at' => now(), 'failure_reason' => null]);
        $source = $disk->path($recording->source_path);
        $directory = 'live-recordings/'.$recording->live_session_id.'/'.$recording->id;
        $disk->makeDirectory($directory.'/hls');
        $mp4Path = $directory.'/recording.mp4';
        $manifestPath = $directory.'/hls/master.m3u8';
        $input = $this->inputArguments($recording, $source);
        $process = new Process([
            (string) config('live-media.recording.ffmpeg_binary', 'ffmpeg'), '-y', ...$input['arguments'],
            ...$input['mapping'], '-c:v', 'libx264', '-preset', 'veryfast', '-c:a', 'aac',
            '-movflags', '+faststart', $disk->path($mp4Path),
        ]);
        $process->setTimeout(3300);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException('FFmpeg processing failed: '.mb_substr($process->getErrorOutput(), 0, 700));
        }
        $recording->update(['progress' => 65]);
        $this->createAdaptiveHls($recording, $disk->path($mp4Path), $disk->path($directory.'/hls'));

        $recording->update([
            'status' => 'ready', 'progress' => 100, 'storage_path' => $mp4Path, 'hls_manifest_path' => $manifestPath,
            'mime_type' => 'video/mp4', 'size_bytes' => $disk->size($mp4Path), 'processed_at' => now(), 'ended_at' => $recording->ended_at ?? now(),
        ]);
    }

    private function createAdaptiveHls(LiveSessionRecording $recording, string $source, string $directory): void
    {
        $process = new Process([
            (string) config('live-media.recording.ffmpeg_binary', 'ffmpeg'), '-y', '-i', $source,
            '-filter_complex', '[0:v]split=2[v720][v360];[v720]scale=-2:720[v720out];[v360]scale=-2:360[v360out]',
            '-map', '[v720out]', '-map', '0:a?', '-map', '[v360out]', '-map', '0:a?',
            '-c:v', 'libx264', '-preset', 'veryfast', '-c:a', 'aac',
            '-b:v:0', '2500k', '-b:v:1', '800k', '-b:a:0', '128k', '-b:a:1', '96k',
            '-f', 'hls', '-hls_time', (string) config('live-media.recording.hls_segment_seconds', 6),
            '-hls_playlist_type', 'vod', '-hls_segment_filename', $directory.'/variant-%v-segment-%05d.ts',
            '-master_pl_name', 'master.m3u8', '-var_stream_map', 'v:0,a:0,name:720p v:1,a:1,name:360p',
            $directory.'/variant-%v.m3u8',
        ]);
        $process->setTimeout(3300);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException('Adaptive HLS processing failed: '.mb_substr($process->getErrorOutput(), 0, 700));
        }
        $recording->update(['progress' => 90]);
    }

    private function inputArguments(LiveSessionRecording $recording, string $source): array
    {
        if (! str_ends_with($source, '.json')) {
            return ['arguments' => ['-i', $source], 'mapping' => []];
        }

        $manifest = json_decode((string) file_get_contents($source), true, flags: JSON_THROW_ON_ERROR);
        $directory = dirname($source);
        $tracks = collect($manifest['tracks'] ?? [])->filter(fn ($track) => isset($track['path']))->values();
        $video = $tracks->where('kind', 'video')->sortByDesc(fn ($track) => ($track['source'] ?? '') === 'screen')->first();
        $audio = $tracks->where('kind', 'audio')->values();
        if (! $video && $audio->isEmpty()) {
            throw new RuntimeException('Server recording contains no playable tracks.');
        }

        $selected = collect([$video])->filter()->merge($audio);
        $arguments = $selected->flatMap(fn ($track) => ['-i', $directory.'/'.basename($track['path'])])->all();
        $videoIndex = $video ? 0 : null;
        $audioStart = $video ? 1 : 0;
        $mapping = [];
        if ($videoIndex !== null) {
            $mapping = ['-map', '0:v:0'];
        } else {
            $arguments = [...$arguments, '-f', 'lavfi', '-i', 'color=c=black:s=1280x720:r=25'];
            $mapping = ['-map', $audio->count().':v:0'];
        }
        if ($audio->count() === 1) {
            $mapping = [...$mapping, '-map', $audioStart.':a:0'];
        } elseif ($audio->count() > 1) {
            $labels = collect(range($audioStart, $audioStart + $audio->count() - 1))->map(fn ($index) => "[{$index}:a]")->implode('');
            $mapping = [...$mapping, '-filter_complex', $labels.'amix=inputs='.$audio->count().':duration=longest[aout]', '-map', '[aout]'];
        } else {
            $arguments = [...$arguments, '-f', 'lavfi', '-i', 'anullsrc=channel_layout=stereo:sample_rate=48000'];
            $mapping = [...$mapping, '-map', $selected->count().':a:0', '-shortest'];
        }

        return ['arguments' => $arguments, 'mapping' => $mapping];
    }
}
