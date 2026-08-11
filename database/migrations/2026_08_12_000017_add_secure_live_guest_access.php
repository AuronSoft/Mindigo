<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_session_guest_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'revoked_at', 'expires_at'], 'live_guest_links_active_idx');
        });

        Schema::create('live_session_guests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('guest_link_id')->constrained('live_session_guest_links')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('email')->nullable();
            $table->char('access_token_hash', 64)->unique();
            $table->string('admission_status', 24)->default('waiting');
            $table->foreignId('admitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admitted_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('microphone_enabled')->default(false);
            $table->boolean('camera_enabled')->default(false);
            $table->boolean('screen_sharing')->default(false);
            $table->string('connection_id', 100)->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'admission_status'], 'live_guests_admission_idx');
        });

        Schema::create('live_session_guest_signals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->string('sender_key', 40);
            $table->string('recipient_key', 40);
            $table->string('type', 20);
            $table->json('payload');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'recipient_key', 'consumed_at'], 'live_guest_signals_inbox_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_guest_signals');
        Schema::dropIfExists('live_session_guests');
        Schema::dropIfExists('live_session_guest_links');
    }
};
