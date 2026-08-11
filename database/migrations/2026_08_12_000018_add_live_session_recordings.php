<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_session_recordings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('recording');
            $table->string('mime_type', 100);
            $table->string('storage_disk', 40)->default('local');
            $table->string('storage_path')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->index(['live_session_id', 'status'], 'live_recordings_status_idx');
        });

        Schema::create('live_session_recording_chunks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recording_id')->constrained('live_session_recordings')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('storage_path');
            $table->unsignedBigInteger('size_bytes');
            $table->char('checksum', 64);
            $table->timestamps();
            $table->unique(['recording_id', 'sequence'], 'live_recording_chunk_sequence_unique');
        });

        Schema::table('live_session_participants', function (Blueprint $table): void {
            $table->timestamp('recording_consented_at')->nullable()->after('force_muted_at');
        });
        Schema::table('live_session_guests', function (Blueprint $table): void {
            $table->timestamp('recording_consented_at')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('live_session_guests', fn (Blueprint $table) => $table->dropColumn('recording_consented_at'));
        Schema::table('live_session_participants', fn (Blueprint $table) => $table->dropColumn('recording_consented_at'));
        Schema::dropIfExists('live_session_recording_chunks');
        Schema::dropIfExists('live_session_recordings');
    }
};
