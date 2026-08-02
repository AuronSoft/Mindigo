<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_classroom_assignments', function (Blueprint $table): void {
            $table->timestamp('starts_at')->nullable()->after('assigned_at')->index();
            $table->timestamp('due_at')->nullable()->after('starts_at')->index();
            $table->boolean('is_mandatory')->default(true)->after('due_at');
            $table->string('visibility', 20)->default('visible')->after('is_mandatory')->index();
        });

        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->foreignId('distribution_id')->nullable()->after('classroom_id')
                ->constrained('course_classroom_assignments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('distribution_id');
        });

        Schema::table('course_classroom_assignments', function (Blueprint $table): void {
            $table->dropColumn(['starts_at', 'due_at', 'is_mandatory', 'visibility']);
        });
    }
};
