<?php

namespace Mindigo\SubjectManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class Subject extends Model
{
    use SoftDeletes;

    public const STATUSES = ['active', 'inactive'];

    public const COLORS = ['green', 'sky', 'amber', 'rose', 'slate'];

    protected $fillable = [
        'created_by',
        'name',
        'code',
        'slug',
        'description',
        'color',
        'icon',
        'status',
        'sort_order',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(SubjectTopic::class)->orderBy('sort_order')->orderBy('name');
    }
}
