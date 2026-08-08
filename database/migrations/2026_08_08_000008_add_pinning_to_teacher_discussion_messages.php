<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teacher_discussion_messages', 'is_pinned')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->boolean('is_pinned')->default(false)->after('read_at');
            });
        }

        if (! Schema::hasColumn('teacher_discussion_messages', 'pinned_at')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            });
        }

        if (! Schema::hasColumn('teacher_discussion_messages', 'pinned_by')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->foreignId('pinned_by')->nullable()->after('pinned_at')->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasIndex('teacher_discussion_messages', 'discussion_message_thread_pinned_idx')) {
            Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
                $table->index(['thread_id', 'is_pinned'], 'discussion_message_thread_pinned_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('teacher_discussion_messages', function (Blueprint $table): void {
            $table->dropIndex('discussion_message_thread_pinned_idx');
            $table->dropConstrainedForeignId('pinned_by');
            $table->dropColumn(['is_pinned', 'pinned_at']);
        });
    }
};
