<?php

namespace Mindigo\TeacherLiveSession\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;

class LiveSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'live_sessions';

    protected $fillable = [
        'classroom_id',
        'teacher_id',
        'title',
        'description',
        'room_name',
        'provider',
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
    ];

    // Relationships

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attendances()
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
}
