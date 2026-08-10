<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table): void {
            $table->string('type', 20)->default('standalone')->after('teacher_id')->index();
            $table->foreignId('course_id')->nullable()->after('type')->constrained('courses')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->after('course_id')->constrained('subjects')->nullOnDelete();
            $table->index(['teacher_id', 'type', 'course_id'], 'classrooms_owner_type_course_index');
        });

        DB::table('classrooms')->orderBy('id')->eachById(function (object $classroom): void {
            $subjectId = DB::table('classroom_subjects')
                ->where('classroom_id', $classroom->id)
                ->orderBy('id')
                ->value('subject_id');

            DB::table('classrooms')->where('id', $classroom->id)->update(['subject_id' => $subjectId]);
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table): void {
            $table->dropIndex('classrooms_owner_type_course_index');
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('course_id');
            $table->dropColumn('type');
        });
    }
};
