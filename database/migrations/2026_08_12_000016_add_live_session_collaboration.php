<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_session_participants', function (Blueprint $table): void {
            $table->timestamp('hand_raised_at')->nullable()->after('connection_id');
            $table->timestamp('force_muted_at')->nullable()->after('hand_raised_at');
            $table->index(['live_session_id', 'hand_raised_at'], 'live_participants_hand_idx');
        });

        Schema::create('live_session_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['live_session_id', 'id'], 'live_messages_stream_idx');
        });

        Schema::create('live_session_room_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('type', 30);
            $table->json('payload')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'id'], 'live_room_events_stream_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_room_events');
        Schema::dropIfExists('live_session_messages');
        Schema::table('live_session_participants', function (Blueprint $table): void {
            $table->dropIndex('live_participants_hand_idx');
            $table->dropColumn(['hand_raised_at', 'force_muted_at']);
        });
    }
};
