<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherLiveSession\Enums\LiveSessionProvider;
use Mindigo\TeacherLiveSession\Enums\LiveSessionType;
use Mindigo\TeacherLiveSession\Enums\ProviderSyncStatus;

class LiveSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'live_sessions';

    protected $fillable = [
        'classroom_id',
        'classroom_schedule_id',
        'session_type',
        'teacher_id',
        'created_by',
        'ended_by',
        'title',
        'description',
        'room_name',
        'idempotency_key',
        'provider',
        'provider_meeting_id',
        'provider_join_url',
        'provider_host_url',
        'provider_metadata',
        'provider_status',
        'fallback_provider',
        'sync_status',
        'last_synced_at',
        'sync_error',
        'room_settings',
        'scheduled_start',
        'scheduled_end',
        'started_at',
        'ended_at',
        'status', // scheduled | live | ended | cancelled
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'provider' => LiveSessionProvider::class,
        'fallback_provider' => LiveSessionProvider::class,
        'sync_status' => ProviderSyncStatus::class,
        'provider_metadata' => 'array',
        'room_settings' => 'array',
        'provider_host_url' => 'encrypted',
        'session_type' => LiveSessionType::class,
    ];

    // Relationships

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassroomSchedule::class, 'classroom_schedule_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(LiveSessionAttendance::class);
    }

    // Scopes

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Helpers

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isEnded(): bool
    {
        return in_array($this->status, ['ended', 'cancelled']);
    }

    public function canJoin(): bool
    {
        return in_array($this->status, ['scheduled', 'live']);
    }

    public function usesExternalProvider(): bool
    {
        return $this->provider->isExternal();
    }
}
