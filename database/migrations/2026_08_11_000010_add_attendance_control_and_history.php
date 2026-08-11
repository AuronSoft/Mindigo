<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_attendance_sessions', function (Blueprint $table): void {
            $table->unsignedSmallInteger('late_after_minutes')->default(15)->after('expires_at');
        });

        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->unsignedSmallInteger('late_minutes')->nullable()->after('status');
            $table->string('absence_reason', 500)->nullable()->after('late_minutes');
            $table->foreignId('updated_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
        });

        Schema::create('classroom_attendance_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_id')->constrained('classroom_attendances')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('old_values')->nullable();
            $table->json('new_values');
            $table->string('change_reason', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['attendance_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_attendance_revisions');
        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['late_minutes', 'absence_reason']);
        });
        Schema::table('classroom_attendance_sessions', fn (Blueprint $table) => $table->dropColumn('late_after_minutes'));
    }
};
