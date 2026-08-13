<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_session_recordings', function (Blueprint $table): void {
            $table->string('capture_mode', 24)->default('client')->after('status');
            $table->unsignedTinyInteger('progress')->default(0)->after('capture_mode');
            $table->string('gateway_recording_id', 191)->nullable()->after('progress');
            $table->string('source_path')->nullable()->after('storage_path');
            $table->string('hls_manifest_path')->nullable()->after('source_path');
            $table->unsignedTinyInteger('processing_attempts')->default(0)->after('duration_seconds');
            $table->timestamp('processing_started_at')->nullable()->after('ended_at');
            $table->timestamp('processed_at')->nullable()->after('processing_started_at');
            $table->index(['status', 'processing_started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('live_session_recordings', function (Blueprint $table): void {
            $table->dropIndex(['status', 'processing_started_at']);
            $table->dropColumn(['capture_mode', 'progress', 'gateway_recording_id', 'source_path', 'hls_manifest_path', 'processing_attempts', 'processing_started_at', 'processed_at']);
        });
    }
};
