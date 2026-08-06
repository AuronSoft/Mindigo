<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('internal_note')->nullable()->after('reviewed_at');
            $table->text('status_note')->nullable()->after('internal_note');
            $table->index(['status', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table): void {
            $table->dropIndex(['status', 'reviewed_at']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['reviewed_at', 'internal_note', 'status_note']);
        });
    }
};
