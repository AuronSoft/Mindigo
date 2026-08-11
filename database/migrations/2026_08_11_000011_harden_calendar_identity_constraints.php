<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_attendances', fn (Blueprint $table) => $table->string('identity_key', 191)->nullable()->after('id'));
        DB::table('classroom_attendances')->orderByDesc('id')->get()->each(function (object $record): void {
            $canonical = $record->classroom_schedule_id
                ? "schedule:{$record->classroom_schedule_id}:student:{$record->student_id}"
                : "classroom:{$record->classroom_id}:date:{$record->session_date}:student:{$record->student_id}";
            $exists = DB::table('classroom_attendances')->where('identity_key', $canonical)->exists();
            DB::table('classroom_attendances')->where('id', $record->id)->update([
                'identity_key' => $exists ? "legacy:{$record->id}:{$canonical}" : $canonical,
            ]);
        });
        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->string('identity_key', 191)->nullable(false)->change();
            $table->unique('identity_key', 'attendance_identity_unique');
            $table->index(['classroom_id', 'session_date', 'status'], 'attendance_report_lookup');
        });

        Schema::table('academic_calendar_exceptions', function (Blueprint $table): void {
            $table->string('scope_key', 191)->nullable()->after('id');
        });
        DB::table('academic_calendar_exceptions')->orderByDesc('id')->get()->each(function (object $exception): void {
            $scope = $exception->classroom_id ? "classroom:{$exception->classroom_id}" : ($exception->course_id ? "course:{$exception->course_id}" : 'global:0');
            $canonical = "{$scope}:{$exception->kind}:{$exception->exception_date}";
            $exists = DB::table('academic_calendar_exceptions')->where('scope_key', $canonical)->exists();
            DB::table('academic_calendar_exceptions')->where('id', $exception->id)->update([
                'scope_key' => $exists ? "legacy:{$exception->id}:{$canonical}" : $canonical,
            ]);
        });
        Schema::table('academic_calendar_exceptions', function (Blueprint $table): void {
            $table->string('scope_key', 191)->nullable(false)->change();
            $table->unique('scope_key', 'calendar_exception_scope_unique');
        });

        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->string('slot_key', 191)->nullable()->after('id');
            $table->index(['session_date', 'status', 'classroom_id'], 'calendar_range_lookup');
        });
        DB::table('classroom_schedules')
            ->whereIn('status', ['scheduled', 'completed'])
            ->orderByDesc('id')
            ->get()
            ->each(function (object $schedule): void {
                $canonical = "classroom:{$schedule->classroom_id}:date:{$schedule->session_date}:start:".substr((string) $schedule->start_time, 0, 5);
                $exists = DB::table('classroom_schedules')->where('slot_key', $canonical)->exists();
                DB::table('classroom_schedules')->where('id', $schedule->id)->update([
                    'slot_key' => $exists ? "legacy:{$schedule->id}:{$canonical}" : $canonical,
                ]);
            });
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->unique('slot_key', 'classroom_schedule_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->dropUnique('classroom_schedule_slot_unique');
            $table->dropIndex('calendar_range_lookup');
            $table->dropColumn('slot_key');
        });
        Schema::table('academic_calendar_exceptions', function (Blueprint $table): void {
            $table->dropUnique('calendar_exception_scope_unique');
            $table->dropColumn('scope_key');
        });
        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->dropUnique('attendance_identity_unique');
            $table->dropIndex('attendance_report_lookup');
            $table->dropColumn('identity_key');
        });
    }
};
