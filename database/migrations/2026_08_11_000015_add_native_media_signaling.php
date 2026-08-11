<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_session_participants', function (Blueprint $table): void {
            $table->boolean('microphone_enabled')->default(false)->after('last_seen_at');
            $table->boolean('camera_enabled')->default(false)->after('microphone_enabled');
            $table->boolean('screen_sharing')->default(false)->after('camera_enabled');
            $table->string('connection_id', 100)->nullable()->after('screen_sharing');
            $table->index(['live_session_id', 'last_seen_at'], 'live_participants_presence_idx');
        });

        Schema::create('live_session_signals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);
            $table->json('payload');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'recipient_id', 'consumed_at'], 'live_signals_inbox_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_signals');
        Schema::table('live_session_participants', function (Blueprint $table): void {
            $table->dropIndex('live_participants_presence_idx');
            $table->dropColumn(['microphone_enabled', 'camera_enabled', 'screen_sharing', 'connection_id']);
        });
    }
};
