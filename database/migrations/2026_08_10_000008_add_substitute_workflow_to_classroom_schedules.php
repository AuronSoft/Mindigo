<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->string('substitute_status', 20)->nullable()->after('substitute_teacher_id')->index();
            $table->timestamp('substitute_responded_at')->nullable()->after('substitute_status');
            $table->string('substitute_response_note', 500)->nullable()->after('substitute_responded_at');
        });

        DB::table('classroom_schedules')->whereNotNull('substitute_teacher_id')->update([
            'substitute_status' => 'accepted',
            'substitute_responded_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->dropIndex(['substitute_status']);
            $table->dropColumn(['substitute_status', 'substitute_responded_at', 'substitute_response_note']);
        });
    }
};
