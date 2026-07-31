<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_admission_programs', function (Blueprint $table) {
            $table->string('source_name')->nullable()->after('source_url');
            $table->timestamp('published_at')->nullable()->after('source_name');
            $table->timestamp('verified_at')->nullable()->after('published_at')->index();
            $table->string('source_hash', 64)->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('learning_admission_programs', function (Blueprint $table) {
            $table->dropColumn(['source_name', 'published_at', 'verified_at', 'source_hash']);
        });
    }
};
