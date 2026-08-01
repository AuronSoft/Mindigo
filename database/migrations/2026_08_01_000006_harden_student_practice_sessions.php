<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('last_activity_at')->index();
            $table->string('request_fingerprint', 64)->nullable()->after('adaptive_context');
            $table->index(
                ['student_id', 'status', 'request_fingerprint'],
                'practice_attempt_idempotency_index'
            );
        });

        DB::table('student_practice_attempts')
            ->where('status', 'in_progress')
            ->whereNull('expires_at')
            ->update(['expires_at' => now()->addMinutes((int) config('practice.session.ttl_minutes'))]);
    }

    public function down(): void
    {
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->dropIndex('practice_attempt_idempotency_index');
            $table->dropIndex(['expires_at']);
            $table->dropColumn(['expires_at', 'request_fingerprint']);
        });
    }
};
