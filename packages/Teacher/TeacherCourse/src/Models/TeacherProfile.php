<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id', 'headline', 'biography', 'specialization', 'experience_years', 'qualifications', 'social_links', 'is_public',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'qualifications' => 'array',
            'social_links' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
