<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_session_attendances', function (Blueprint $table): void {
            $table->unsignedInteger('microphone_seconds')->default(0)->after('poll_votes_count');
            $table->unsignedInteger('camera_seconds')->default(0)->after('microphone_seconds');
            $table->timestamp('media_last_counted_at')->nullable()->after('camera_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('live_session_attendances', function (Blueprint $table): void {
            $table->dropColumn(['microphone_seconds', 'camera_seconds', 'media_last_counted_at']);
        });
    }
};
