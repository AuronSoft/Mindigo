<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->unique();
            $table->string('code', 40)->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('color', ['green', 'sky', 'amber', 'rose', 'slate'])->default('green');
            $table->string('icon', 80)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subject_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['subject_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_topics');
        Schema::dropIfExists('subjects');
    }
};
