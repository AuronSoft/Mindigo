<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('question_bank_questions')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE question_bank_questions MODIFY type ENUM('single_choice','multiple_choice','true_false','short_answer','essay') NOT NULL DEFAULT 'single_choice'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('question_bank_questions')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE question_bank_questions MODIFY type ENUM('single_choice','multiple_choice','true_false','short_answer') NOT NULL DEFAULT 'single_choice'");
        }
    }
};
