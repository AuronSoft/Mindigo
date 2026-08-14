<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class ExamTemplate extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_READY = 'ready';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_READY, self::STATUS_ARCHIVED];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['ready_at' => 'datetime', 'total_points' => 'decimal:2'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ExamTemplateVersion::class);
    }

    public function sessions(): HasManyThrough
    {
        return $this->hasManyThrough(ExamSession::class, ExamTemplateVersion::class);
    }

    public function isEditable(): bool
    {
        return $this->status !== self::STATUS_ARCHIVED;
    }
}
