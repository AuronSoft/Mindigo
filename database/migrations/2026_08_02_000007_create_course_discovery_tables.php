<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->boolean('is_featured')->default(false)->index()->after('rating_count');
            $table->unsignedInteger('featured_order')->default(0)->index()->after('is_featured');
            $table->timestamp('featured_at')->nullable()->index()->after('featured_order');
        });

        Schema::create('course_wishlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('course_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('view_count')->default(1);
            $table->timestamp('last_viewed_at')->index();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('course_searches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('keyword', 120)->index();
            $table->timestamp('searched_at')->index();
            $table->timestamps();
            $table->index(['user_id', 'searched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_searches');
        Schema::dropIfExists('course_views');
        Schema::dropIfExists('course_wishlists');

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn(['is_featured', 'featured_order', 'featured_at']);
        });
    }
};
