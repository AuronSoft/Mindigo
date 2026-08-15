<?php

namespace Mindigo\ExamManagement\Services;

use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\SystemSetting\Models\SystemSetting;

class ExamCutoverService
{
    public const MODE_PARALLEL = 'parallel';

    public const MODE_NEW = 'new';

    public const MODES = [self::MODE_PARALLEL, self::MODE_NEW];

    public function mode(): string
    {
        $mode = SystemSetting::query()->where('key', 'exam_cutover_mode')->first()?->typedValue();

        return in_array($mode, self::MODES, true) ? $mode : self::MODE_PARALLEL;
    }

    public function betaTeacherIds(): array
    {
        return array_values(array_unique(array_map('intval', (array) SystemSetting::query()->where('key', 'exam_beta_teacher_ids')->first()?->typedValue())));
    }

    public function prefersNew(?User $user): bool
    {
        if ($this->mode() === self::MODE_NEW) {
            return true;
        }
        $teacherIds = $this->betaTeacherIds();
        if ($teacherIds === [] || ! $user) {
            return false;
        }
        if ($user->isTeacher()) {
            return in_array((int) $user->getAuthIdentifier(), $teacherIds, true);
        }

        return $user->isStudent() && ExamCandidate::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->whereHas('session', fn ($query) => $query->whereIn('organizer_id', $teacherIds))
            ->exists();
    }

    public function legacyWritable(?User $user): bool
    {
        return $this->mode() === self::MODE_PARALLEL && ! $this->prefersNew($user);
    }

    public function configure(string $mode, array $betaTeacherIds = []): void
    {
        abort_unless(in_array($mode, self::MODES, true), 422);
        SystemSetting::query()->updateOrCreate(['key' => 'exam_cutover_mode'], ['group' => 'exam', 'value' => $mode, 'type' => 'string']);
        SystemSetting::query()->updateOrCreate(['key' => 'exam_beta_teacher_ids'], ['group' => 'exam', 'value' => json_encode(array_values(array_unique(array_map('intval', $betaTeacherIds)))), 'type' => 'json']);
    }
}
