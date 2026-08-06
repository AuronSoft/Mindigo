<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->indexExists()) {
            return;
        }

        Schema::table('teacher_application_interviews', function (Blueprint $table): void {
            $table->index(['teacher_application_id', 'scheduled_at'], 'tai_app_schedule_idx');
        });
    }

    public function down(): void
    {
        if (! $this->indexExists()) {
            return;
        }

        Schema::table('teacher_application_interviews', function (Blueprint $table): void {
            $table->dropIndex('tai_app_schedule_idx');
        });
    }

    private function indexExists(): bool
    {
        if (! Schema::hasTable('teacher_application_interviews')) {
            return false;
        }

        return collect(Schema::getIndexes('teacher_application_interviews'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === 'tai_app_schedule_idx');
    }
};
