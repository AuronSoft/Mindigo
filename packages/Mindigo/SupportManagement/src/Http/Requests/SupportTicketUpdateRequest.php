<?php

namespace Mindigo\SupportManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Mindigo\SupportManagement\Models\SupportTicket;

class SupportTicketUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->isAdmin() ?? false)
            && ($this->user()?->hasPermissionTo('support-tickets.manage') ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(SupportTicket::STATUSES)],
            'priority' => ['required', Rule::in(SupportTicket::PRIORITIES)],
            'category' => ['required', Rule::in(SupportTicket::CATEGORIES)],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'admin_note' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
