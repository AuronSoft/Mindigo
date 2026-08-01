<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_practice_sets', function (Blueprint $table): void {
            $table->string('status', 20)->default('ready')->after('source')->index();
        });

        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->foreignId('practice_set_id')->nullable()->after('student_id')
                ->constrained('learning_practice_sets')->nullOnDelete();
            $table->string('status', 20)->default('in_progress')->after('score')->index();
            $table->timestamp('last_activity_at')->nullable()->after('started_at');
            $table->index(['student_id', 'status'], 'practice_attempt_student_status_index');
        });

        DB::table('student_practice_attempts')
            ->whereNotNull('completed_at')
            ->update(['status' => 'completed']);

        DB::table('student_practice_attempts')
            ->whereNull('last_activity_at')
            ->update(['last_activity_at' => DB::raw('started_at')]);
    }

    public function down(): void
    {
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->dropIndex('practice_attempt_student_status_index');
            $table->dropConstrainedForeignId('practice_set_id');
            $table->dropColumn(['status', 'last_activity_at']);
        });

        Schema::table('learning_practice_sets', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
