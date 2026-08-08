<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_discussion_threads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->unsignedBigInteger('teacher_id')->nullable()->after('id');
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_discussion_threads', function (Blueprint $table) {
            $table->dropIndex(['teacher_id']);
            $table->dropConstrainedForeignId('teacher_id');
            $table->foreignId('teacher_id')->nullable(false)->constrained('users')->cascadeOnDelete();
        });
    }
};