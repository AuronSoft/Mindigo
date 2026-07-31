<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_score_scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('combination_code', 10);
            $table->json('subject_scores');
            $table->decimal('priority_score', 4, 2)->default(0);
            $table->decimal('bonus_score', 4, 2)->default(0);
            $table->decimal('total_score', 5, 2);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('learning_universities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('short_name', 80)->nullable();
            $table->string('province', 120)->nullable()->index();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('learning_admission_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained('learning_universities')->cascadeOnDelete();
            $table->string('major_code', 30)->nullable()->index();
            $table->string('major_name')->index();
            $table->unsignedSmallInteger('year')->index();
            $table->string('method', 120);
            $table->json('combinations')->nullable();
            $table->decimal('benchmark_score', 5, 2)->nullable();
            $table->unsignedInteger('quota')->nullable();
            $table->string('source_url')->nullable();
            $table->timestamps();
            $table->index(['university_id', 'year']);
        });

        Schema::create('learning_admission_favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admission_program_id')->constrained('learning_admission_programs')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'admission_program_id'], 'learning_admission_favorite_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_admission_favorites');
        Schema::dropIfExists('learning_admission_programs');
        Schema::dropIfExists('learning_universities');
        Schema::dropIfExists('learning_score_scenarios');
    }
};
