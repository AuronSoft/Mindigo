<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_session_attempt_answers', function (Blueprint $table): void {
            $table->unsignedInteger('response_seconds')->nullable()->after('answer');
            $table->timestamp('answered_at')->nullable()->after('response_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('exam_session_attempt_answers', fn (Blueprint $table) => $table->dropColumn(['response_seconds', 'answered_at']));
    }
};
