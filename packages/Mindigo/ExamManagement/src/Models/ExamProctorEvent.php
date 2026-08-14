<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class ExamProctorEvent extends Model
{
    public const SOURCE_CLIENT = 'client';

    public const SOURCE_SERVER = 'server';

    public const SOURCE_PROCTOR = 'proctor';

    public const RISK_LOW = 'low';

    public const RISK_MEDIUM = 'medium';

    public const RISK_HIGH = 'high';

    public const TYPE_TAB_HIDDEN = 'tab_hidden';

    public const TYPE_FULLSCREEN_EXITED = 'fullscreen_exited';

    public const TYPE_COPY_ATTEMPT = 'copy_attempt';

    public const TYPE_PASTE_ATTEMPT = 'paste_attempt';

    public const TYPE_CONCURRENT_SESSION = 'concurrent_session';

    public const TYPE_IP_CHANGED = 'ip_changed';

    public const TYPE_DEVICE_CHANGED = 'device_changed';

    public const TYPE_HEARTBEAT_MISSED = 'heartbeat_missed';

    public const TYPE_CONNECTION_LOST = 'connection_lost';

    public const TYPE_ABNORMAL_REFRESH = 'abnormal_refresh';

    public const TYPE_CAMERA_CONSENT_GRANTED = 'camera_consent_granted';

    public const TYPE_CAMERA_CONSENT_DENIED = 'camera_consent_denied';

    public const TYPE_CAMERA_SNAPSHOT = 'camera_snapshot';

    public const TYPE_PROCTOR_NOTE = 'proctor_note';

    public const TYPE_ATTEMPT_TERMINATED = 'attempt_terminated';

    public const CLIENT_TYPES = [
        self::TYPE_TAB_HIDDEN, self::TYPE_FULLSCREEN_EXITED, self::TYPE_COPY_ATTEMPT,
        self::TYPE_PASTE_ATTEMPT, self::TYPE_ABNORMAL_REFRESH,
    ];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'occurred_at' => 'datetime'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamSessionAttempt::class, 'exam_session_attempt_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
