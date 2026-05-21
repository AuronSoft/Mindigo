<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('subject')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('color', 24)->default('green');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['created_by', 'subject']);
        });

        Schema::table('question_bank_questions', function (Blueprint $table) {
            $table->foreignId('folder_id')
                ->nullable()
                ->after('reviewed_by')
                ->constrained('question_bank_folders')
                ->nullOnDelete();

            $table->index(['folder_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('question_bank_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
        });

        Schema::dropIfExists('question_bank_folders');
    }
};
