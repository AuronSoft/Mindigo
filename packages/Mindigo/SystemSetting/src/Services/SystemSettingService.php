<?php

namespace Mindigo\SystemSetting\Services;

use Mindigo\SystemSetting\Models\SystemSetting;

class SystemSettingService
{
    private array $lastChanges = [];

    public function definitions(): array
    {
        return [
            'general' => [
                'label' => __('Mindigo-system-setting::app.definitions.general.label'),
                'description' => __('Mindigo-system-setting::app.definitions.general.description'),
                'settings' => [
                    'site_name' => ['label' => __('Mindigo-system-setting::app.settings.site_name'), 'type' => 'string', 'default' => 'MindingoLMS'],
                    'support_email' => ['label' => __('Mindigo-system-setting::app.settings.support_email'), 'type' => 'email', 'default' => 'support@mindigo.test'],
                    'support_phone' => ['label' => __('Mindigo-system-setting::app.settings.support_phone'), 'type' => 'string', 'default' => ''],
                    'default_locale' => [
                        'label' => __('Mindigo-system-setting::app.settings.default_locale'),
                        'type' => 'select',
                        'default' => 'vi',
                        'options' => [
                            'vi' => __('Mindigo-system-setting::app.options.vi'),
                            'en' => __('Mindigo-system-setting::app.options.en'),
                        ],
                    ],
                    'timezone' => [
                        'label' => __('Mindigo-system-setting::app.settings.timezone'),
                        'type' => 'select',
                        'default' => 'Asia/Ho_Chi_Minh',
                        'options' => ['Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh', 'UTC' => 'UTC'],
                    ],
                ],
            ],
            'exam' => [
                'label' => __('Mindigo-system-setting::app.definitions.exam.label'),
                'description' => __('Mindigo-system-setting::app.definitions.exam.description'),
                'settings' => [
                    'default_exam_duration' => ['label' => __('Mindigo-system-setting::app.settings.default_exam_duration'), 'type' => 'integer', 'default' => 45],
                    'default_pass_score' => ['label' => __('Mindigo-system-setting::app.settings.default_pass_score'), 'type' => 'integer', 'default' => 60],
                    'max_attempts' => ['label' => __('Mindigo-system-setting::app.settings.max_attempts'), 'type' => 'integer', 'default' => 3],
                    'shuffle_questions' => ['label' => __('Mindigo-system-setting::app.settings.shuffle_questions'), 'type' => 'boolean', 'default' => true],
                    'shuffle_answers' => ['label' => __('Mindigo-system-setting::app.settings.shuffle_answers'), 'type' => 'boolean', 'default' => true],
                    'show_result_after_submit' => ['label' => __('Mindigo-system-setting::app.settings.show_result_after_submit'), 'type' => 'boolean', 'default' => true],
                    'show_answer_explanation' => ['label' => __('Mindigo-system-setting::app.settings.show_answer_explanation'), 'type' => 'boolean', 'default' => true],
                    'auto_submit_when_timeout' => ['label' => __('Mindigo-system-setting::app.settings.auto_submit_when_timeout'), 'type' => 'boolean', 'default' => true],
                ],
            ],
            'user' => [
                'label' => __('Mindigo-system-setting::app.definitions.user.label'),
                'description' => __('Mindigo-system-setting::app.definitions.user.description'),
                'settings' => [
                    'allow_student_registration' => ['label' => __('Mindigo-system-setting::app.settings.allow_student_registration'), 'type' => 'boolean', 'default' => true],
                    'default_register_role' => [
                        'label' => __('Mindigo-system-setting::app.settings.default_register_role'),
                        'type' => 'select',
                        'default' => 'student',
                        'options' => [
                            'student' => __('Mindigo-system-setting::app.options.student'),
                            'teacher' => __('Mindigo-system-setting::app.options.teacher'),
                        ],
                    ],
                    'teacher_can_create_exam' => ['label' => __('Mindigo-system-setting::app.settings.teacher_can_create_exam'), 'type' => 'boolean', 'default' => true],
                    'teacher_exam_requires_approval' => ['label' => __('Mindigo-system-setting::app.settings.teacher_exam_requires_approval'), 'type' => 'boolean', 'default' => true],
                    'require_email_verification' => ['label' => __('Mindigo-system-setting::app.settings.require_email_verification'), 'type' => 'boolean', 'default' => false],
                ],
            ],
            'notification' => [
                'label' => __('Mindigo-system-setting::app.definitions.notification.label'),
                'description' => __('Mindigo-system-setting::app.definitions.notification.description'),
                'settings' => [
                    'enable_system_notifications' => ['label' => __('Mindigo-system-setting::app.settings.enable_system_notifications'), 'type' => 'boolean', 'default' => true],
                    'notify_new_exam' => ['label' => __('Mindigo-system-setting::app.settings.notify_new_exam'), 'type' => 'boolean', 'default' => true],
                    'notify_exam_result' => ['label' => __('Mindigo-system-setting::app.settings.notify_exam_result'), 'type' => 'boolean', 'default' => true],
                    'notify_security' => ['label' => __('Mindigo-system-setting::app.settings.notify_security'), 'type' => 'boolean', 'default' => true],
                    'mail_from_name' => ['label' => __('Mindigo-system-setting::app.settings.mail_from_name'), 'type' => 'string', 'default' => 'MindingoLMS'],
                ],
            ],
            'ai_security' => [
                'label' => __('Mindigo-system-setting::app.definitions.ai_security.label'),
                'description' => __('Mindigo-system-setting::app.definitions.ai_security.description'),
                'settings' => [
                    'enable_ai_features' => ['label' => __('Mindigo-system-setting::app.settings.enable_ai_features'), 'type' => 'boolean', 'default' => true],
                    'ai_generate_questions' => ['label' => __('Mindigo-system-setting::app.settings.ai_generate_questions'), 'type' => 'boolean', 'default' => true],
                    'ai_review_questions' => ['label' => __('Mindigo-system-setting::app.settings.ai_review_questions'), 'type' => 'boolean', 'default' => true],
                    'ai_daily_limit' => ['label' => __('Mindigo-system-setting::app.settings.ai_daily_limit'), 'type' => 'integer', 'default' => 100],
                    'session_lifetime' => ['label' => __('Mindigo-system-setting::app.settings.session_lifetime'), 'type' => 'integer', 'default' => 120],
                    'max_login_attempts' => ['label' => __('Mindigo-system-setting::app.settings.max_login_attempts'), 'type' => 'integer', 'default' => 5],
                ],
            ],
        ];
    }

    public function groupedSettings(): array
    {
        $stored = SystemSetting::query()->get()->keyBy('key');

        return collect($this->definitions())->map(function (array $group, string $groupKey) use ($stored) {
            $group['settings'] = collect($group['settings'])->map(function (array $setting, string $key) use ($stored, $groupKey) {
                $model = $stored->get($key);
                $setting['key'] = $key;
                $setting['group'] = $groupKey;
                $setting['value'] = $model ? $model->typedValue() : $setting['default'];

                return $setting;
            })->all();

            return $group;
        })->all();
    }

    public function update(array $settings): int
    {
        $changed = 0;
        $this->lastChanges = [];
        $stored = SystemSetting::query()->get()->keyBy('key');

        foreach ($this->definitions() as $groupKey => $group) {
            foreach ($group['settings'] as $key => $definition) {
                $value = $settings[$key] ?? ($definition['type'] === 'boolean' ? false : $definition['default']);
                $normalizedValue = $this->normalizeValue($value, $definition['type']);
                $normalizedDefault = $this->normalizeValue($definition['default'], $definition['type']);
                $model = $stored->get($key);

                if (! $model && $normalizedValue === $normalizedDefault) {
                    continue;
                }

                if ($model && $model->value === $normalizedValue) {
                    continue;
                }

                $this->lastChanges[$key] = [
                    'group' => $groupKey,
                    'label' => $definition['label'],
                    'old' => $model ? $model->typedValue() : $definition['default'],
                    'new' => $this->typedValue($normalizedValue, $definition['type']),
                ];

                SystemSetting::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'group' => $groupKey,
                        'value' => $normalizedValue,
                        'type' => $this->storageType($definition['type']),
                    ]
                );

                $changed++;
            }
        }

        return $changed;
    }

    public function changes(): array
    {
        return $this->lastChanges;
    }

    public function value(string $key, mixed $default = null): mixed
    {
        $setting = SystemSetting::query()->where('key', $key)->first();

        return $setting?->typedValue() ?? $default;
    }

    private function normalizeValue(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) max(0, (int) $value),
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE),
            default => trim((string) $value),
        };
    }

    private function storageType(string $type): string
    {
        return $type === 'select' || $type === 'email' ? 'string' : $type;
    }

    private function typedValue(string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value ?: '[]', true),
            default => $value,
        };
    }
}
