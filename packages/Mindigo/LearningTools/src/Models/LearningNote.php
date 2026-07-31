<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\SubjectManagement\Models\SubjectTopic;

class LearningNote extends Model
{
    use SoftDeletes;

    protected $fillable = ['owner_id', 'subject_id', 'subject_topic_id', 'title', 'content', 'is_pinned'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(SubjectTopic::class, 'subject_topic_id');
    }
}
