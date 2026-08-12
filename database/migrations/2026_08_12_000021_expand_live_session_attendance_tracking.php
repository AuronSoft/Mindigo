<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_session_attendances', function (Blueprint $table): void {
            $table->dateTime('last_seen_at')->nullable()->after('left_at');
            $table->unsignedInteger('total_seconds')->default(0)->after('last_seen_at');
            $table->unsignedSmallInteger('join_count')->default(0)->after('total_seconds');
            $table->unsignedSmallInteger('late_minutes')->default(0)->after('join_count');
            $table->string('attendance_status', 20)->default('absent')->after('late_minutes');
            $table->unsignedInteger('chat_messages_count')->default(0)->after('attendance_status');
            $table->unsignedInteger('reactions_count')->default(0)->after('chat_messages_count');
            $table->unsignedInteger('hands_raised_count')->default(0)->after('reactions_count');
            $table->unsignedInteger('poll_votes_count')->default(0)->after('hands_raised_count');
            $table->dateTime('finalized_at')->nullable()->after('poll_votes_count');
            $table->index(['live_session_id', 'attendance_status'], 'live_attendance_session_status_idx');
        });

        Schema::create('live_session_attendance_segments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_id')->constrained('live_session_attendances')->cascadeOnDelete();
            $table->dateTime('joined_at');
            $table->dateTime('last_seen_at');
            $table->dateTime('left_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('leave_reason', 30)->nullable();
            $table->timestamps();
            $table->index(['attendance_id', 'left_at'], 'live_attendance_segment_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_attendance_segments');
        Schema::table('live_session_attendances', function (Blueprint $table): void {
            $table->dropIndex('live_attendance_session_status_idx');
            $table->dropColumn(['last_seen_at', 'total_seconds', 'join_count', 'late_minutes', 'attendance_status', 'chat_messages_count', 'reactions_count', 'hands_raised_count', 'poll_votes_count', 'finalized_at']);
        });
    }
};
