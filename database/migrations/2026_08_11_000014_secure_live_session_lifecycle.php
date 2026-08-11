<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table): void {
            $table->string('status', 24)->default('scheduled')->change();
            $table->timestamp('locked_at')->nullable()->after('ended_at');
            $table->timestamp('cancelled_at')->nullable()->after('locked_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable()->after('cancelled_by');
            $table->text('failure_reason')->nullable()->after('cancel_reason');
            $table->unsignedInteger('join_token_version')->default(1)->after('failure_reason');
            $table->index(['status', 'scheduled_start'], 'live_sessions_lifecycle_index');
        });

        Schema::create('live_session_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('role', 24);
            $table->string('admission_status', 24)->default('waiting');
            $table->foreignId('admitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admitted_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['live_session_id', 'user_id'], 'live_session_participant_user_unique');
            $table->index(['live_session_id', 'admission_status'], 'live_session_participant_admission_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_participants');
        Schema::table('live_sessions', function (Blueprint $table): void {
            $table->dropIndex('live_sessions_lifecycle_index');
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['locked_at', 'cancelled_at', 'cancelled_by', 'cancel_reason', 'failure_reason', 'join_token_version']);
        });
    }
};
