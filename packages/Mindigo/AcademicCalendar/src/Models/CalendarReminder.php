<?php

namespace Mindigo\AcademicCalendar\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarReminder extends Model
{
    protected $table = 'academic_calendar_reminders';

    protected $fillable = ['user_id', 'event_id', 'reminder_key', 'scheduled_for', 'sent_at'];

    protected function casts(): array
    {
        return ['scheduled_for' => 'datetime', 'sent_at' => 'datetime'];
    }
}
