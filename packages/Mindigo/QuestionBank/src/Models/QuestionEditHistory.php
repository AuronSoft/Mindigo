<?php

// packages/Mindigo/QuestionBank/src/Models/QuestionEditHistory.php

namespace Mindigo\QuestionBank\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\Auth\Models\User;

class QuestionEditHistory extends Model
{
    protected $table = 'question_edit_histories';

    protected $fillable = ['question_id', 'edited_by', 'action', 'changes', 'note'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // Label màu cho action
    public function actionColor(): string
    {
        return match ($this->action) {
            'create' => 'bg-green-100 text-green-700',
            'update' => 'bg-blue-100 text-blue-700',
            'review' => 'bg-amber-100 text-amber-700',
            'import' => 'bg-purple-100 text-purple-700',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'create' => 'Tạo mới',
            'update' => 'Chỉnh sửa',
            'review' => 'Duyệt',
            'import' => 'Import',
            default => $this->action,
        };
    }
}
