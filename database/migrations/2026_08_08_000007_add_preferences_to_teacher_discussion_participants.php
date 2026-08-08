<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('teacher_discussion_participants', 'is_muted')) {
            Schema::table('teacher_discussion_participants', function (Blueprint $table): void {
                $table->boolean('is_muted')->default(false)->after('last_read_at');
            });
        }

        if (! Schema::hasColumn('teacher_discussion_participants', 'is_pinned')) {
            Schema::table('teacher_discussion_participants', function (Blueprint $table): void {
                $table->boolean('is_pinned')->default(false)->after('is_muted');
            });
        }

        if (! Schema::hasColumn('teacher_discussion_participants', 'pinned_at')) {
            Schema::table('teacher_discussion_participants', function (Blueprint $table): void {
                $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            });
        }

        if (! Schema::hasIndex('teacher_discussion_participants', 'discussion_participant_user_pinned_idx')) {
            Schema::table('teacher_discussion_participants', function (Blueprint $table): void {
                $table->index(['user_id', 'is_pinned'], 'discussion_participant_user_pinned_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('teacher_discussion_participants', 'discussion_participant_user_pinned_idx')) {
            Schema::table('teacher_discussion_participants', function (Blueprint $table): void {
                $table->dropIndex('discussion_participant_user_pinned_idx');
            });
        }

        Schema::table('teacher_discussion_participants', function (Blueprint $table): void {
            $table->dropColumn(['is_muted', 'is_pinned', 'pinned_at']);
        });
    }
};
