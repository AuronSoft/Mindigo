<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_discussion_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('teacher_discussion_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Vai trò trong hội thoại: owner, admin, member
            $table->string('role')->default('member')->index();
            $table->timestamp('joined_at')->nullable();
            // Mốc đã đọc tới đâu (dùng cho unread + read receipt)
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['thread_id', 'user_id']);
            $table->index(['user_id', 'last_read_at']);
        });

        // Backfill: với mỗi thread lớp học đã có, tự sinh participant
        // gồm giáo viên (owner) + các học sinh đang active trong lớp.
        $threads = DB::table('teacher_discussion_threads')->get(['id', 'teacher_id', 'classroom_id', 'created_by']);

        foreach ($threads as $thread) {
            $now = now();
            $inserts = [];

            // Giáo viên quản lý lớp là owner
            $inserts[] = [
                'thread_id' => $thread->id,
                'user_id' => $thread->teacher_id,
                'role' => 'owner',
                'joined_at' => $now,
                'last_read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Học sinh active trong lớp là member
            $students = DB::table('classroom_students')
                ->where('classroom_id', $thread->classroom_id)
                ->where('status', 'active')
                ->where('student_id', '!=', $thread->teacher_id)
                ->pluck('student_id');

            foreach ($students as $studentId) {
                $inserts[] = [
                    'thread_id' => $thread->id,
                    'user_id' => $studentId,
                    'role' => 'member',
                    'joined_at' => $now,
                    'last_read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($inserts !== []) {
                DB::table('teacher_discussion_participants')->insert($inserts);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_discussion_participants');
    }
};
