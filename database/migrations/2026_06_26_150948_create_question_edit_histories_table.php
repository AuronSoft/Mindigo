<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_edit_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('edited_by');
            $table->string('action', 50)->default('update'); // create|update|review|import
            $table->json('changes')->nullable(); // ['field' => ['old'=>..., 'new'=>...]]
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('question_bank_questions')->onDelete('cascade');
            $table->index(['question_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_edit_histories');
    }
};
