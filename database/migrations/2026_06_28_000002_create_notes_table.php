<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->longText('content')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['student_id', 'updated_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
