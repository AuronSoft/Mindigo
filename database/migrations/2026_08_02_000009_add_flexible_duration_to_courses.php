<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->decimal('duration_value', 8, 2)->nullable()->after('estimated_duration_minutes');
            $table->string('duration_unit', 20)->nullable()->after('duration_value');
            $table->index('duration_unit');
        });

        DB::table('courses')->whereNotNull('estimated_duration_minutes')->update([
            'duration_value' => DB::raw('estimated_duration_minutes'),
            'duration_unit' => 'minute',
        ]);
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex(['duration_unit']);
            $table->dropColumn(['duration_value', 'duration_unit']);
        });
    }
};
