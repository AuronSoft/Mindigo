<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->timestamp('assignment_notified_at')->nullable()->after('published_at');
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->timestamp('last_activity_at')->nullable()->after('expires_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->dropIndex(['last_activity_at']);
            $table->dropColumn('last_activity_at');
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn('assignment_notified_at');
        });
    }
};
