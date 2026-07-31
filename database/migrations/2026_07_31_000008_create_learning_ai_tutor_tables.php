<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('subject')->nullable()->index();
            $table->string('mode', 30)->default('explain');
            $table->string('provider_conversation_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('learning_ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('learning_ai_conversations')->cascadeOnDelete();
            $table->string('role', 20);
            $table->longText('content');
            $table->string('status', 20)->default('completed');
            $table->string('provider_response_id')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_ai_messages');
        Schema::dropIfExists('learning_ai_conversations');
    }
};
