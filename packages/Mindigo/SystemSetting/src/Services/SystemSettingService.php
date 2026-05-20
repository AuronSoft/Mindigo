<?php

namespace Mindigo\SystemSetting\Services;

use Mindigo\SystemSetting\Models\SystemSetting;

class SystemSettingService
{
    public function definitions(): array
    {
        return [
            'general' => [
                'label' => 'Tổng quan',
                'description' => 'Thông tin nhận diện và vận hành chung của hệ thống.',
                'settings' => [
                    'site_name' => ['label' => 'Tên hệ thống', 'type' => 'string', 'default' => 'MindigoExam'],
                    'support_email' => ['label' => 'Email hỗ trợ', 'type' => 'email', 'default' => 'support@mindigo.test'],
                    'support_phone' => ['label' => 'Số điện thoại hỗ trợ', 'type' => 'string', 'default' => ''],
                    'default_locale' => ['label' => 'Ngôn ngữ mặc định', 'type' => 'select', 'default' => 'vi', 'options' => ['vi' => 'Tiếng Việt', 'en' => 'English']],
                    'timezone' => ['label' => 'Múi giờ', 'type' => 'select', 'default' => 'Asia/Ho_Chi_Minh', 'options' => ['Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh', 'UTC' => 'UTC']],
                ],
            ],
            'exam' => [
                'label' => 'Thi trắc nghiệm',
                'description' => 'Cấu hình mặc định cho đề thi, bài luyện tập và cách hiển thị kết quả.',
                'settings' => [
                    'default_exam_duration' => ['label' => 'Thời gian làm bài mặc định (phút)', 'type' => 'integer', 'default' => 45],
                    'default_pass_score' => ['label' => 'Điểm đạt mặc định (%)', 'type' => 'integer', 'default' => 60],
                    'max_attempts' => ['label' => 'Số lần làm lại mặc định', 'type' => 'integer', 'default' => 3],
                    'shuffle_questions' => ['label' => 'Trộn câu hỏi', 'type' => 'boolean', 'default' => true],
                    'shuffle_answers' => ['label' => 'Trộn đáp án', 'type' => 'boolean', 'default' => true],
                    'show_result_after_submit' => ['label' => 'Hiển thị kết quả ngay sau khi nộp', 'type' => 'boolean', 'default' => true],
                    'show_answer_explanation' => ['label' => 'Cho phép xem giải thích đáp án', 'type' => 'boolean', 'default' => true],
                    'auto_submit_when_timeout' => ['label' => 'Tự động nộp bài khi hết giờ', 'type' => 'boolean', 'default' => true],
                ],
            ],
            'user' => [
                'label' => 'Người dùng & phân quyền',
                'description' => 'Điều khiển đăng ký, vai trò mặc định và quyền thao tác theo nhóm người dùng.',
                'settings' => [
                    'allow_student_registration' => ['label' => 'Cho phép học viên tự đăng ký', 'type' => 'boolean', 'default' => true],
                    'default_register_role' => ['label' => 'Vai trò mặc định khi đăng ký', 'type' => 'select', 'default' => 'student', 'options' => ['student' => 'Học viên', 'teacher' => 'Giáo viên']],
                    'teacher_can_create_exam' => ['label' => 'Giáo viên được tạo đề thi', 'type' => 'boolean', 'default' => true],
                    'teacher_exam_requires_approval' => ['label' => 'Đề của giáo viên cần admin duyệt', 'type' => 'boolean', 'default' => true],
                    'require_email_verification' => ['label' => 'Bắt buộc xác minh email', 'type' => 'boolean', 'default' => false],
                ],
            ],
            'notification' => [
                'label' => 'Thông báo',
                'description' => 'Bật/tắt các thông báo chính trong hệ thống ôn thi.',
                'settings' => [
                    'enable_system_notifications' => ['label' => 'Bật thông báo hệ thống', 'type' => 'boolean', 'default' => true],
                    'notify_new_exam' => ['label' => 'Thông báo đề thi mới', 'type' => 'boolean', 'default' => true],
                    'notify_exam_result' => ['label' => 'Thông báo kết quả bài làm', 'type' => 'boolean', 'default' => true],
                    'notify_security' => ['label' => 'Thông báo tài khoản và bảo mật', 'type' => 'boolean', 'default' => true],
                    'mail_from_name' => ['label' => 'Tên người gửi email', 'type' => 'string', 'default' => 'MindigoExam'],
                ],
            ],
            'ai_security' => [
                'label' => 'AI & bảo mật',
                'description' => 'Kiểm soát tính năng AI, phiên đăng nhập và giới hạn bảo vệ tài khoản.',
                'settings' => [
                    'enable_ai_features' => ['label' => 'Bật tính năng AI', 'type' => 'boolean', 'default' => true],
                    'ai_generate_questions' => ['label' => 'AI tạo câu hỏi từ tài liệu', 'type' => 'boolean', 'default' => true],
                    'ai_review_questions' => ['label' => 'AI kiểm tra chất lượng câu hỏi', 'type' => 'boolean', 'default' => true],
                    'ai_daily_limit' => ['label' => 'Giới hạn lượt AI mỗi ngày', 'type' => 'integer', 'default' => 100],
                    'session_lifetime' => ['label' => 'Thời gian phiên đăng nhập (phút)', 'type' => 'integer', 'default' => 120],
                    'max_login_attempts' => ['label' => 'Số lần đăng nhập sai tối đa', 'type' => 'integer', 'default' => 5],
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
}
