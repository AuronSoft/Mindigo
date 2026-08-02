<?php

namespace Mindigo\TeacherCourse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class CourseSearch extends Model
{
    protected $fillable = ['user_id', 'keyword', 'searched_at'];

    protected function casts(): array
    {
        return ['searched_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
