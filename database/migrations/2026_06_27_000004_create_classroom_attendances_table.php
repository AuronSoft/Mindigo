<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('session_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['classroom_id', 'student_id', 'session_date'], 'classroom_student_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_attendances');
    }
};
