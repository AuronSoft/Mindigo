<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('teacher_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('headline', 180)->nullable();
            $table->text('biography')->nullable();
            $table->string('specialization', 255)->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->json('qualifications')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->foreignId('subject_id')->nullable()->after('teacher_id')->constrained('subjects')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('subject_id')->constrained('course_categories')->nullOnDelete();
            $table->string('publication_status', 30)->default('draft')->after('status')->index();
            $table->boolean('is_active')->default(true)->after('publication_status')->index();
            $table->string('education_level', 50)->nullable()->after('description')->index();
            $table->string('difficulty', 30)->default('beginner')->after('education_level')->index();
            $table->string('language', 10)->default('vi')->after('difficulty')->index();
            $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('language');
            $table->json('learning_outcomes')->nullable()->after('estimated_duration_minutes');
            $table->json('requirements')->nullable()->after('learning_outcomes');
            $table->json('target_learners')->nullable()->after('requirements');
            $table->timestamp('submitted_for_review_at')->nullable()->after('target_learners');
            $table->timestamp('published_at')->nullable()->after('submitted_for_review_at')->index();
            $table->foreignId('published_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            $table->index(['teacher_id', 'publication_status', 'is_active'], 'courses_owner_publication_active_index');
            $table->index(['subject_id', 'education_level', 'difficulty'], 'courses_catalog_filter_index');
        });

        DB::table('courses')->orderBy('id')->eachById(function (object $course): void {
            DB::table('courses')->where('id', $course->id)->update([
                'is_active' => $course->status === 'active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex('courses_owner_publication_active_index');
            $table->dropIndex('courses_catalog_filter_index');
            $table->dropConstrainedForeignId('published_by');
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('subject_id');
            $table->dropColumn([
                'publication_status',
                'is_active',
                'education_level',
                'difficulty',
                'language',
                'estimated_duration_minutes',
                'learning_outcomes',
                'requirements',
                'target_learners',
                'submitted_for_review_at',
                'published_at',
            ]);
        });

        Schema::dropIfExists('teacher_profiles');
        Schema::dropIfExists('course_categories');
    }
};
