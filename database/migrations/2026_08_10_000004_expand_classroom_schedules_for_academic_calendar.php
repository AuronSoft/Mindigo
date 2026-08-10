<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->foreignId('lesson_id')->nullable()->after('classroom_id')->constrained('lessons')->nullOnDelete();
            $table->string('delivery_mode', 20)->default('offline')->after('type')->index();
            $table->string('status', 20)->default('scheduled')->after('delivery_mode')->index();
            $table->string('location')->nullable()->after('end_time');
            $table->string('meeting_url', 2048)->nullable()->after('location');
            $table->text('cancel_reason')->nullable()->after('makeup_reason');
            $table->foreignId('substitute_teacher_id')->nullable()->after('cancel_reason')->constrained('users')->nullOnDelete();
            $table->foreignId('makeup_for_schedule_id')->nullable()->after('substitute_teacher_id')->constrained('classroom_schedules')->nullOnDelete();
            $table->foreignId('rescheduled_from_id')->nullable()->after('makeup_for_schedule_id')->constrained('classroom_schedules')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->after('rescheduled_from_id');
            $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->index(['session_date', 'start_time', 'end_time'], 'classroom_schedule_time_range_index');
        });
    }

    public function down(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->dropIndex('classroom_schedule_time_range_index');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('rescheduled_from_id');
            $table->dropConstrainedForeignId('makeup_for_schedule_id');
            $table->dropConstrainedForeignId('substitute_teacher_id');
            $table->dropConstrainedForeignId('lesson_id');
            $table->dropColumn([
                'delivery_mode', 'status', 'location', 'meeting_url', 'cancel_reason', 'published_at',
            ]);
        });
    }
};
