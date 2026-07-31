<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $table = 'learning_ai_messages';

    protected $fillable = ['conversation_id', 'role', 'content', 'status', 'provider_response_id', 'input_tokens', 'output_tokens', 'error_message'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
