<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_gpa_scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->json('courses');
            $table->unsignedSmallInteger('total_credits');
            $table->decimal('average_ten', 4, 2);
            $table->decimal('gpa_four', 3, 2);
            $table->string('classification', 30);
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_gpa_scenarios');
    }
};
