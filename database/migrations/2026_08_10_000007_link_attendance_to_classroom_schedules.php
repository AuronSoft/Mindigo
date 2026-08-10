<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_attendance_sessions', function (Blueprint $table): void {
            $table->index('classroom_id', 'attendance_sessions_classroom_fk_index');
        });

        Schema::table('classroom_attendance_sessions', function (Blueprint $table): void {
            $table->dropUnique(['classroom_id', 'session_date']);
            $table->foreignId('classroom_schedule_id')->nullable()->after('classroom_id')->constrained('classroom_schedules')->nullOnDelete();
            $table->unique('classroom_schedule_id');
        });

        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->index('classroom_id', 'attendances_classroom_fk_index');
        });

        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->dropUnique('classroom_student_session_unique');
            $table->foreignId('classroom_schedule_id')->nullable()->after('classroom_id')->constrained('classroom_schedules')->nullOnDelete();
            $table->unique(['classroom_schedule_id', 'student_id'], 'schedule_student_attendance_unique');
        });

        DB::table('classroom_attendance_sessions')->orderBy('id')->each(function (object $session): void {
            $scheduleIds = DB::table('classroom_schedules')
                ->where('classroom_id', $session->classroom_id)
                ->whereDate('session_date', $session->session_date)
                ->pluck('id');

            if ($scheduleIds->count() === 1) {
                DB::table('classroom_attendance_sessions')->where('id', $session->id)->update(['classroom_schedule_id' => $scheduleIds->first()]);
                DB::table('classroom_attendances')->where('attendance_session_id', $session->id)->update(['classroom_schedule_id' => $scheduleIds->first()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->dropUnique('schedule_student_attendance_unique');
            $table->dropConstrainedForeignId('classroom_schedule_id');
            $table->unique(['classroom_id', 'student_id', 'session_date'], 'classroom_student_session_unique');
        });
        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->dropIndex('attendances_classroom_fk_index');
        });
        Schema::table('classroom_attendance_sessions', function (Blueprint $table): void {
            $table->dropUnique(['classroom_schedule_id']);
            $table->dropConstrainedForeignId('classroom_schedule_id');
            $table->unique(['classroom_id', 'session_date']);
        });
        Schema::table('classroom_attendance_sessions', function (Blueprint $table): void {
            $table->dropIndex('attendance_sessions_classroom_fk_index');
        });
    }
};
