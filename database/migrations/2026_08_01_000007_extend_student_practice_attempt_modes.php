<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->enum('mode', ['subject', 'topic', 'skill', 'mixed'])
                ->default('mixed')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('student_practice_attempts')
            ->where('mode', 'skill')
            ->update(['mode' => 'mixed']);

        Schema::table('student_practice_attempts', function (Blueprint $table): void {
            $table->enum('mode', ['subject', 'topic', 'mixed'])
                ->default('mixed')
                ->change();
        });
    }
};
