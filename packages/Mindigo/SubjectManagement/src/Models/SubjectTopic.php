<?php

namespace Mindigo\SubjectManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectTopic extends Model
{
    use SoftDeletes;

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'subject_id',
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
