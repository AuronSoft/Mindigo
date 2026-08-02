<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->string('access_type', 20)->default('free')->after('target_learners')->index();
            $table->decimal('price', 12, 2)->default(0)->after('access_type');
            $table->string('currency', 3)->default('VND')->after('price');
            $table->unsignedBigInteger('view_count')->default(0)->after('currency')->index();
            $table->unsignedBigInteger('enrollment_count')->default(0)->after('view_count')->index();
            $table->decimal('rating_average', 3, 2)->default(0)->after('enrollment_count')->index();
            $table->unsignedInteger('rating_count')->default(0)->after('rating_average');
            $table->index(
                ['publication_status', 'is_active', 'published_at'],
                'courses_public_catalog_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropIndex('courses_public_catalog_index');
            $table->dropColumn([
                'access_type',
                'price',
                'currency',
                'view_count',
                'enrollment_count',
                'rating_average',
                'rating_count',
            ]);
        });
    }
};
