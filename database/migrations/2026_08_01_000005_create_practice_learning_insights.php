<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_practice_insights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('practice_skill_id')->nullable()->constrained('practice_skills')->cascadeOnDelete();
            $table->string('fingerprint', 120);
            $table->string('type', 30)->index();
            $table->string('insight_code', 60);
            $table->unsignedTinyInteger('priority')->default(50)->index();
            $table->json('metrics');
            $table->string('engine_version', 20)->default('analytics_v1');
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
            $table->unique(['student_id', 'fingerprint'], 'student_practice_insight_unique');
            $table->index(['student_id', 'status', 'priority'], 'student_practice_insight_feed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_practice_insights');
    }
};
