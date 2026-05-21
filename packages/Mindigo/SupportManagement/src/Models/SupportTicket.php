<?php

namespace Mindigo\SupportManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mindigo\Auth\Models\User;

class SupportTicket extends Model
{
    use SoftDeletes;

    public const STATUSES = ['open', 'in_progress', 'resolved', 'closed'];
    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];
    public const CATEGORIES = ['account', 'exam', 'result', 'technical', 'content', 'other'];

    protected $fillable = [
        'ticket_code',
        'user_id',
        'user_name',
        'user_email',
        'assigned_to',
        'category',
        'priority',
        'status',
        'subject',
        'message',
        'admin_note',
        'last_replied_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'last_replied_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
