<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mindigo\Auth\Models\User;

class ExamTemplateVersion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['settings' => 'array', 'locked_at' => 'datetime', 'total_points' => 'decimal:2'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ExamTemplate::class, 'exam_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ExamSection::class)->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamTemplateQuestion::class)->orderBy('sort_order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null || $this->sessions()->exists();
    }
}
