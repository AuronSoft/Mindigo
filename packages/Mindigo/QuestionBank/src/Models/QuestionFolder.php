<?php

namespace Mindigo\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class QuestionFolder extends Model
{
    use SoftDeletes;

    protected $table = 'question_bank_folders';

    protected $fillable = [
        'created_by',
        'name',
        'subject',
        'description',
        'color',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'folder_id');
    }
}
