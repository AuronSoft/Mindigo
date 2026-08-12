<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_session_whiteboard_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'id'], 'live_whiteboard_stream_idx');
        });

        Schema::create('live_session_polls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('question', 500);
            $table->string('status', 20)->default('open');
            $table->boolean('show_results')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'status'], 'live_polls_status_idx');
        });
        Schema::create('live_session_poll_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_id')->constrained('live_session_polls')->cascadeOnDelete();
            $table->string('label', 300);
            $table->unsignedTinyInteger('position');
        });
        Schema::create('live_session_poll_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_id')->constrained('live_session_polls')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('live_session_poll_options')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['poll_id', 'user_id'], 'live_poll_user_vote_unique');
        });

        Schema::create('live_session_resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('mime_type', 120);
            $table->string('storage_disk', 40)->default('local');
            $table->string('storage_path');
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum', 64);
            $table->timestamps();
            $table->index(['live_session_id', 'id'], 'live_resources_session_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_resources');
        Schema::dropIfExists('live_session_poll_votes');
        Schema::dropIfExists('live_session_poll_options');
        Schema::dropIfExists('live_session_polls');
        Schema::dropIfExists('live_session_whiteboard_actions');
    }
};
