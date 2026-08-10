<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table): void {
            $table->boolean('notif_calendar_updates')->default(true)->after('notif_system_news');
            $table->boolean('notif_calendar_reminders')->default(true)->after('notif_calendar_updates');
        });

        Schema::create('academic_calendar_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_id');
            $table->string('reminder_key', 32);
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'event_id', 'reminder_key'], 'calendar_reminder_delivery_unique');
            $table->index(['scheduled_for', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendar_reminders');
        Schema::table('notification_preferences', function (Blueprint $table): void {
            $table->dropColumn(['notif_calendar_updates', 'notif_calendar_reminders']);
        });
    }
};
