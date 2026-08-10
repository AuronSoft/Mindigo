<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->date('ends_at')->nullable()->after('starts_at')->index();
        });

        Schema::create('academic_calendar_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('exception_date')->index();
            $table->string('kind', 30)->default('no_class')->index();
            $table->string('title', 255);
            $table->string('reason', 1000)->nullable();
            $table->timestamps();
            $table->index(['course_id', 'exception_date']);
            $table->index(['classroom_id', 'exception_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendar_exceptions');
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex(['ends_at']);
            $table->dropColumn('ends_at');
        });
    }
};
