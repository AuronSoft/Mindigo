<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->date('starts_at')->nullable()->after('currency');
            $table->json('schedule_days')->nullable()->after('starts_at');
            $table->string('study_time', 120)->nullable()->after('schedule_days');
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex(['starts_at']);
            $table->dropColumn(['starts_at', 'schedule_days', 'study_time']);
        });
    }
};
