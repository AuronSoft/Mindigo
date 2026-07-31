<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_focus_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->unsignedSmallInteger('planned_minutes')->default(25);
            $table->unsignedSmallInteger('focus_minutes')->default(0);
            $table->unsignedSmallInteger('break_minutes')->default(5);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['running', 'completed', 'cancelled'])->default('running');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'started_at']);
        });

        Schema::create('learning_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('subject_topic_id')->nullable()->constrained('subject_topics')->nullOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_id', 'is_pinned', 'updated_at']);
        });

        Schema::create('learning_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('subject_topic_id')->nullable()->constrained('subject_topics')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['subject_id', 'subject_topic_id']);
        });

        Schema::create('learning_resource_favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learning_resource_id')->constrained('learning_resources')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'learning_resource_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_resource_favorites');
        Schema::dropIfExists('learning_resources');
        Schema::dropIfExists('learning_notes');
        Schema::dropIfExists('learning_focus_sessions');
    }
};
