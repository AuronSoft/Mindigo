<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSection extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['shuffle_questions' => 'boolean'];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ExamTemplateVersion::class, 'exam_template_version_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamTemplateQuestion::class)->orderBy('sort_order');
    }
}
