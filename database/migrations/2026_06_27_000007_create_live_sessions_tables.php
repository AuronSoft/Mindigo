<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng buổi học trực tuyến
        if (! Schema::hasTable('live_sessions')) {
            Schema::create('live_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();

                // Tên phòng dùng cho Jitsi (không trùng, không đoán được)
                $table->string('room_name')->unique();
                $table->string('provider')->default('jitsi'); // jitsi | zoom | meet ...

                $table->dateTime('scheduled_start');
                $table->dateTime('scheduled_end')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('ended_at')->nullable();

                // scheduled | live | ended | cancelled
                $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Bảng điểm danh buổi học
        if (! Schema::hasTable('live_session_attendances')) {
            Schema::create('live_session_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_session_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->dateTime('joined_at')->nullable();
                $table->dateTime('left_at')->nullable();
                $table->timestamps();

                // Mỗi người 1 bản ghi điểm danh / buổi
                $table->unique(['live_session_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_attendances');
        Schema::dropIfExists('live_sessions');
    }
};
