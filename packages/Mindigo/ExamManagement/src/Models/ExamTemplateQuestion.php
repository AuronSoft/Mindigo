<?php

namespace Mindigo\ExamManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mindigo\QuestionBank\Models\Question;

class ExamTemplateQuestion extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['options' => 'array', 'correct_answers' => 'array', 'rubric' => 'array', 'points' => 'decimal:2'];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ExamTemplateVersion::class, 'exam_template_version_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ExamSection::class, 'exam_section_id');
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'source_question_id');
    }
}
