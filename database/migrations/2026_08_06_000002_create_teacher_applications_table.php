<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('application_code')->unique();
            $table->string('status')->index();
            $table->string('application_type')->index();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone', 30);
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->nullOnDelete();
            $table->string('education_level')->nullable();
            $table->string('specialization')->nullable();
            $table->json('teaching_skills')->nullable();
            $table->string('teaching_mode')->index();
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->string('current_organization')->nullable();
            $table->text('previous_organizations')->nullable();
            $table->text('achievements')->nullable();
            $table->text('experience_description')->nullable();
            $table->json('verification_documents')->nullable();
            $table->text('teaching_method')->nullable();
            $table->string('intro_video_url')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_applications');
    }
};
