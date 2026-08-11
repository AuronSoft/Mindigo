<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table): void {
            $table->string('session_type', 24)->default('flexible')->after('classroom_schedule_id')->index();
            $table->json('room_settings')->nullable()->after('sync_error');
        });

        DB::table('live_sessions')
            ->whereIn('classroom_id', DB::table('classrooms')->select('id')->where('type', 'course'))
            ->update(['session_type' => 'regular']);
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table): void {
            $table->dropIndex(['session_type']);
            $table->dropColumn(['session_type', 'room_settings']);
        });
    }
};
