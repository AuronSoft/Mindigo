<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')->nullable()->after('needs_review')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->foreignId('released_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable()->after('released_by')->index();
        });

        Schema::table('exam_session_attempt_answers', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')->nullable()->after('feedback')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('exam_session_attempt_answers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('reviewed_at');
        });

        Schema::table('exam_session_attempts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('released_by');
            $table->dropColumn(['reviewed_at', 'released_at']);
        });
    }
};
