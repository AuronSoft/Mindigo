<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_review_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('review_status', 32)->index();
            $table->text('review_note')->nullable();
            $table->string('publication_state_before', 32);
            $table->string('publication_state_after', 32);
            $table->timestamp('reviewed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['course_id', 'created_at']);
            $table->index(['review_status', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_review_histories');
    }
};
