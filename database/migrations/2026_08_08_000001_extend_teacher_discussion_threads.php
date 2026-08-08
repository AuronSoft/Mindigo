<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $usesSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        if ($usesSqlite) {
            Schema::table('teacher_discussion_threads', function (Blueprint $table): void {
                $table->dropIndex('teacher_discussion_threads_teacher_id_last_message_at_index');
            });
        }

        Schema::table('teacher_discussion_threads', function (Blueprint $table) {
            // Thả ràng buộc cũ để cho phép classroom_id nullable (direct/group không cần lớp)
            $table->dropUnique(['teacher_id', 'classroom_id']);
            $table->dropConstrainedForeignId('classroom_id');
            $table->unsignedBigInteger('classroom_id')->nullable()->after('teacher_id');
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();

            // Loại hội thoại: class (lớp học), direct (1-1), group (nhóm tuỳ chỉnh)
            $table->string('type')->default('class')->after('classroom_id')->index();
            // Tên hiển thị cho nhóm/direct tuỳ chỉnh; để trống -> dùng tên lớp/người dùng
            $table->string('name')->nullable()->after('type');
            // Ảnh đại diện nhóm (storage path hoặc URL)
            $table->string('avatar')->nullable()->after('name');
            // Màu chủ đề cuộc trò chuyện (preset)
            $table->string('theme_color')->nullable()->after('avatar');
            // Mô tả ngắn cho nhóm
            $table->string('description')->nullable()->after('theme_color');
            // Người tạo thread (admin/teacher/student)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('description');

            $table->index(['type', 'last_message_at']);
        });

        // SQLite rebuilds the table again in the following migration when
        // teacher_id becomes nullable. Recreating the legacy composite index
        // here would leave that rebuild referencing the column being replaced.
    }

    public function down(): void
    {
        Schema::table('teacher_discussion_threads', function (Blueprint $table) {
            $table->dropIndex(['type', 'last_message_at']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['type', 'name', 'avatar', 'theme_color', 'description']);

            $table->dropConstrainedForeignId('classroom_id');
            $table->foreignId('classroom_id')->nullable(false)->constrained('classrooms')->cascadeOnDelete();
            $table->unique(['teacher_id', 'classroom_id']);
        });
    }
};
