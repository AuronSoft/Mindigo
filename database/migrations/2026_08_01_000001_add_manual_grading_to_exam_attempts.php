<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->foreignId('graded_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });

        Schema::table('exam_attempt_answers', function (Blueprint $table): void {
            $table->text('feedback')->nullable()->after('needs_review');
            $table->foreignId('graded_by')->nullable()->after('feedback')->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempt_answers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('graded_by');
            $table->dropColumn(['feedback', 'graded_at']);
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('graded_by');
            $table->dropColumn('graded_at');
        });
    }
};
