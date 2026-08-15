<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_practice_sets', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('title');
            $table->uuid('share_token')->nullable()->unique()->after('status');
            $table->boolean('is_shared')->default(false)->after('share_token')->index();
        });
    }

    public function down(): void
    {
        Schema::table('learning_practice_sets', function (Blueprint $table): void {
            $table->dropColumn(['description', 'share_token', 'is_shared']);
        });
    }
};
