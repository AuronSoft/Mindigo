<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->text('reschedule_reason')->nullable()->after('cancel_reason');
        });
    }

    public function down(): void
    {
        Schema::table('classroom_schedules', function (Blueprint $table): void {
            $table->dropColumn('reschedule_reason');
        });
    }
};
