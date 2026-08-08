<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teacher_discussion_messages', 'reply_to_id')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->foreignId('reply_to_id')->nullable()->after('sender_id')->constrained('teacher_discussion_messages')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('teacher_discussion_messages', 'edited_at')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->timestamp('edited_at')->nullable()->after('body');
            });
        }

        if (! Schema::hasTable('teacher_discussion_message_reactions')) {
            Schema::create('teacher_discussion_message_reactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('message_id')->constrained('teacher_discussion_messages')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('emoji', 32);
                $table->timestamps();

                $table->unique(['message_id', 'user_id', 'emoji'], 'discussion_msg_reaction_unique');
                $table->index(['message_id', 'emoji']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_discussion_message_reactions');

        if (Schema::hasColumn('teacher_discussion_messages', 'edited_at')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->dropColumn('edited_at');
            });
        }

        if (Schema::hasColumn('teacher_discussion_messages', 'reply_to_id')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('reply_to_id');
            });
        }
    }
};
