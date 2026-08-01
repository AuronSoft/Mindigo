<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_skill_progress', function (Blueprint $table): void {
            $table->decimal('mastery_score', 5, 2)->default(0)->after('accuracy');
            $table->string('mastery_level', 30)->default('novice')->after('mastery_score')->index();
            $table->decimal('confidence_score', 5, 2)->default(0)->after('mastery_level');
            $table->string('recommended_difficulty', 30)->default('easy')->after('confidence_score');
            $table->unsignedSmallInteger('consecutive_correct')->default(0)->after('recommended_difficulty');
            $table->unsignedSmallInteger('consecutive_incorrect')->default(0)->after('consecutive_correct');
            $table->string('engine_version', 20)->default('v1')->after('consecutive_incorrect');
            $table->timestamp('last_evaluated_at')->nullable()->after('last_practiced_at');
        });

        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->boolean('is_adaptive')->default(false)->after('selection_strategy')->index();
            $table->decimal('mastery_before', 5, 2)->nullable()->after('is_adaptive');
            $table->decimal('mastery_after', 5, 2)->nullable()->after('mastery_before');
            $table->json('adaptive_context')->nullable()->after('mastery_after');
        });

        Schema::create('student_practice_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('practice_skill_id')->constrained('practice_skills')->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->unsignedTinyInteger('priority')->default(50)->index();
            $table->string('target_difficulty', 30);
            $table->string('reason_code', 60);
            $table->json('reason_context')->nullable();
            $table->string('engine_version', 20)->default('v1');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('generated_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'practice_skill_id'], 'student_practice_recommendation_unique');
            $table->index(['student_id', 'status', 'priority'], 'student_practice_recommendation_feed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_practice_recommendations');
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->dropColumn(['is_adaptive', 'mastery_before', 'mastery_after', 'adaptive_context']);
        });
        Schema::table('student_skill_progress', function (Blueprint $table): void {
            $table->dropColumn([
                'mastery_score', 'mastery_level', 'confidence_score', 'recommended_difficulty',
                'consecutive_correct', 'consecutive_incorrect', 'engine_version', 'last_evaluated_at',
            ]);
        });
    }
};
