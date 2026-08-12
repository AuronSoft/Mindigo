<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_session_breakout_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('status', 20)->default('draft');
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedSmallInteger('duration_minutes')->default(15);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['live_session_id', 'name'], 'live_breakout_session_name_unique');
            $table->index(['live_session_id', 'status'], 'live_breakout_session_status_idx');
        });

        Schema::table('live_session_participants', function (Blueprint $table): void {
            $table->foreignId('breakout_room_id')->nullable()->after('connection_id')
                ->constrained('live_session_breakout_rooms')->nullOnDelete();
        });

        Schema::create('live_session_breakout_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('breakout_room_id')->constrained('live_session_breakout_rooms')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('live_session_participants')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
            $table->unique(['breakout_room_id', 'participant_id'], 'live_breakout_room_participant_unique');
            $table->index(['participant_id', 'left_at'], 'live_breakout_participant_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_breakout_assignments');
        Schema::table('live_session_participants', fn (Blueprint $table) => $table->dropConstrainedForeignId('breakout_room_id'));
        Schema::dropIfExists('live_session_breakout_rooms');
    }
};
