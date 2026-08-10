<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_attendance_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('session_date');
            $table->text('code');
            $table->string('status', 20)->default('open')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['classroom_id', 'session_date']);
        });

        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->foreignId('attendance_session_id')->nullable()->after('student_id')->constrained('classroom_attendance_sessions')->nullOnDelete();
            $table->string('method', 20)->default('manual')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('classroom_attendances', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('attendance_session_id');
            $table->dropColumn('method');
        });
        Schema::dropIfExists('classroom_attendance_sessions');
    }
};
