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
            $table->foreignId('classroom_schedule_id')->nullable()->after('classroom_id')
                ->constrained('classroom_schedules')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('teacher_id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('ended_by')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->nullable()->after('room_name')->unique();
            $table->string('provider_meeting_id')->nullable()->after('provider');
            $table->text('provider_join_url')->nullable()->after('provider_meeting_id');
            $table->text('provider_host_url')->nullable()->after('provider_join_url');
            $table->json('provider_metadata')->nullable()->after('provider_host_url');
            $table->string('provider_status')->nullable()->after('provider_metadata');
            $table->string('fallback_provider')->default('native')->after('provider_status');
            $table->string('sync_status')->default('not_required')->after('fallback_provider');
            $table->timestamp('last_synced_at')->nullable()->after('sync_status');
            $table->text('sync_error')->nullable()->after('last_synced_at');

            $table->unique('classroom_schedule_id', 'live_sessions_schedule_unique');
            $table->index(['provider', 'provider_status'], 'live_sessions_provider_status_index');
            $table->index(['sync_status', 'last_synced_at'], 'live_sessions_sync_status_index');
        });

        DB::table('live_sessions')->where('provider', 'jitsi')->update([
            'provider' => 'native',
            'fallback_provider' => 'native',
            'sync_status' => 'not_required',
            'provider_status' => 'ready',
        ]);

        DB::table('live_sessions')->whereNull('created_by')->update([
            'created_by' => DB::raw('teacher_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table): void {
            $table->dropIndex('live_sessions_provider_status_index');
            $table->dropIndex('live_sessions_sync_status_index');
            $table->dropForeign(['classroom_schedule_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['ended_by']);
            $table->dropUnique('live_sessions_schedule_unique');
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn([
                'classroom_schedule_id',
                'created_by',
                'ended_by',
                'idempotency_key',
                'provider_meeting_id',
                'provider_join_url',
                'provider_host_url',
                'provider_metadata',
                'provider_status',
                'fallback_provider',
                'sync_status',
                'last_synced_at',
                'sync_error',
            ]);
        });
    }
};
