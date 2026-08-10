<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->string('type', 20)->default('regular')->after('classroom_id')->index();
            $table->text('makeup_reason')->nullable()->after('description');
            $table->index(['classroom_id', 'session_date', 'start_time'], 'classroom_schedule_slot_index');
        });
    }

    public function down(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->dropIndex('classroom_schedule_slot_index');
            $table->dropColumn(['type', 'makeup_reason']);
        });
    }
};
