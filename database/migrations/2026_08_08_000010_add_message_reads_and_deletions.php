<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('teacher_discussion_message_reads')) {
            Schema::create('teacher_discussion_message_reads', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('message_id')->constrained('teacher_discussion_messages')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('read_at');

                $table->unique(['message_id', 'user_id'], 'discussion_msg_read_unique');
                $table->index(['user_id', 'read_at']);
            });
        }

        if (! Schema::hasTable('teacher_discussion_message_deletions')) {
            Schema::create('teacher_discussion_message_deletions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('message_id')->constrained('teacher_discussion_messages')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('deleted_at');

                $table->unique(['message_id', 'user_id'], 'discussion_msg_deletion_unique');
                $table->index(['user_id', 'deleted_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_discussion_message_deletions');
        Schema::dropIfExists('teacher_discussion_message_reads');
    }
};
