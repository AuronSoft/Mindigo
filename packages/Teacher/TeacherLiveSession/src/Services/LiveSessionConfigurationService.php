<?php

namespace Mindigo\TeacherLiveSession\Services;

use Illuminate\Support\Facades\DB;
use Mindigo\SystemSetting\Models\SystemSetting;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;

final class LiveSessionConfigurationService
{
    private const DEFINITIONS = [
        'live_google_meet_enabled' => ['boolean', true], 'live_zoom_enabled' => ['boolean', true],
        'live_max_participants' => ['integer', 100], 'live_max_duration_minutes' => ['integer', 240],
        'live_max_sessions_per_teacher_daily' => ['integer', 20], 'live_max_bitrate_kbps' => ['integer', 2500],
        'live_recording_enabled' => ['boolean', true], 'live_recording_max_minutes' => ['integer', 180],
        'live_data_retention_days' => ['integer', 365], 'live_recording_retention_days' => ['integer', 90],
        'live_recording_consent_required' => ['boolean', true],
    ];

    public function all(): array
    {
        $stored = SystemSetting::query()->whereIn('key', array_keys(self::DEFINITIONS))->get()->keyBy('key');

        return collect(self::DEFINITIONS)->mapWithKeys(function (array $definition, string $key) use ($stored): array {
            [$type, $default] = $definition;
            $value = $stored->get($key)?->typedValue() ?? $default;

            return [$key => $type === 'integer' ? (int) $value : (bool) $value];
        })->all();
    }

    public function update(array $values): array
    {
        $before = $this->all();
        DB::transaction(function () use ($values): void {
            foreach (self::DEFINITIONS as $key => [$type, $default]) {
                $value = $values[$key] ?? ($type === 'boolean' ? false : $default);
                SystemSetting::query()->updateOrCreate(['key' => $key], [
                    'group' => 'live_session', 'type' => $type,
                    'value' => $type === 'boolean' ? ($value ? '1' : '0') : (string) max(1, (int) $value),
                ]);
            }
        });

        return ['before' => $before, 'after' => $this->all()];
    }

    public function providerEnabled(LiveSessionProvider $provider): bool
    {
        return match ($provider) {
            LiveSessionProvider::Native => true,
            LiveSessionProvider::GoogleMeet => $this->all()['live_google_meet_enabled'],
            LiveSessionProvider::Zoom => $this->all()['live_zoom_enabled'],
            default => false,
        };
    }

    public function value(string $key): mixed
    {
        return $this->all()[$key] ?? null;
    }
}
