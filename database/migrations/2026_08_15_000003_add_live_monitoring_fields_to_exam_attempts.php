<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->timestamp('paused_at')->nullable()->after('last_activity_at');
            $table->foreignId('paused_by')->nullable()->after('paused_at')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('pause_remaining_seconds')->nullable()->after('paused_by');
            $table->unsignedSmallInteger('added_time_minutes')->default(0)->after('pause_remaining_seconds');
            $table->text('latest_warning')->nullable()->after('added_time_minutes');
            $table->timestamp('latest_warning_at')->nullable()->after('latest_warning');
        });
    }

    public function down(): void
    {
        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('paused_by');
            $table->dropColumn(['paused_at', 'pause_remaining_seconds', 'added_time_minutes', 'latest_warning', 'latest_warning_at']);
        });
    }
};
