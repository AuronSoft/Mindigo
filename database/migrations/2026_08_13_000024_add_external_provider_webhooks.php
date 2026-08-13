<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_provider_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 32);
            $table->string('event_id', 191);
            $table->string('event_type', 120);
            $table->foreignId('live_session_id')->nullable()->constrained('live_sessions')->nullOnDelete();
            $table->longText('payload');
            $table->string('status', 24)->default('pending');
            $table->text('failure_reason')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'event_id']);
            $table->index(['status', 'received_at']);
        });

        Schema::create('live_provider_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('provider_participant_id', 191);
            $table->string('provider_session_id', 191);
            $table->string('display_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_session_id']);
            $table->index(['live_session_id', 'joined_at']);
        });

        Schema::create('live_provider_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('connection_id')->constrained('live_provider_connections')->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('channel_id', 191);
            $table->string('resource_id', 191)->nullable();
            $table->text('resource_uri')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_renewed_at')->nullable();
            $table->string('status', 24)->default('active');
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'channel_id']);
            $table->index(['status', 'expires_at']);
        });

        Schema::table('live_session_recordings', function (Blueprint $table): void {
            $table->string('provider', 32)->nullable()->after('live_session_id');
            $table->string('provider_recording_id', 191)->nullable()->after('provider');
            $table->text('provider_play_url')->nullable()->after('provider_recording_id');
            $table->unique(['provider', 'provider_recording_id']);
        });
    }

    public function down(): void
    {
        Schema::table('live_session_recordings', function (Blueprint $table): void {
            $table->dropUnique(['provider', 'provider_recording_id']);
            $table->dropColumn(['provider', 'provider_recording_id', 'provider_play_url']);
        });
        Schema::dropIfExists('live_provider_subscriptions');
        Schema::dropIfExists('live_provider_participants');
        Schema::dropIfExists('live_provider_webhook_events');
    }
};
