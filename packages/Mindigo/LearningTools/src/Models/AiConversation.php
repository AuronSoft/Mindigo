<?php

namespace Mindigo\LearningTools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiConversation extends Model
{
    use SoftDeletes;

    protected $table = 'learning_ai_conversations';

    protected $fillable = ['user_id', 'title', 'subject', 'mode', 'provider_conversation_id'];

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }
}
