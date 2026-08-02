<?php

namespace Mindigo\SupportManagement\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\SupportManagement\Models\SupportTicket;
use Mindigo\SupportManagement\Models\SupportTicketAttachment;
use Mindigo\SupportManagement\Models\SupportTicketMessage;

class SupportTicketService
{
    public function filteredList(User $user, array $filters)
    {
        $query = SupportTicket::query()
            ->with(['requester:id,name,email,role', 'assignee:id,name,email,role'])
            ->withCount('messages')
            ->latest('updated_at');

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->getAuthIdentifier());
        }

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('ticket_code', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('user_name', 'like', "%{$keyword}%")
                    ->orWhere('user_email', 'like', "%{$keyword}%");
            });
        }

        foreach (['status', 'priority', 'category'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        return $query->paginate(12)->withQueryString();
    }

    public function create(Request $request): SupportTicket
    {
        $validated = $request->validated();
        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $validated, $user): SupportTicket {
            $ticket = SupportTicket::query()->create([
                'ticket_code' => $this->makeTicketCode(),
                'user_id' => $user->getAuthIdentifier(),
                'user_name' => $user->name,
                'user_email' => $user->email,
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'last_replied_at' => now(),
            ]);

            $message = $ticket->messages()->create([
                'user_id' => $user->getAuthIdentifier(),
                'user_name' => $user->name,
                'user_email' => $user->email,
                'sender_role' => $user->role,
                'message' => $validated['message'],
                'is_internal' => false,
            ]);

            $this->storeAttachments($request, $ticket, $message);

            return $ticket;
        });

        $this->audit('create', [], ['ticket' => $ticket->only(['ticket_code', 'subject', 'status', 'priority', 'category'])], $ticket);

        return $ticket;
    }

    public function reply(Request $request, SupportTicket $ticket): SupportTicketMessage
    {
        $validated = $request->validated();
        $user = $request->user();
        $isInternal = $user->isAdmin() && $request->boolean('is_internal');

        $message = DB::transaction(function () use ($request, $ticket, $validated, $user, $isInternal): SupportTicketMessage {
            $message = $ticket->messages()->create([
                'user_id' => $user->getAuthIdentifier(),
                'user_name' => $user->name,
                'user_email' => $user->email,
                'sender_role' => $user->role,
                'message' => $validated['message'],
                'is_internal' => $isInternal,
            ]);

            $ticket->forceFill([
                'last_replied_at' => now(),
                'status' => $ticket->status === 'open' && $user->isAdmin() ? 'in_progress' : $ticket->status,
            ])->save();

            $this->storeAttachments($request, $ticket, $message);

            return $message;
        });

        $this->audit('reply', [], ['message_id' => $message->id, 'is_internal' => $isInternal], $ticket);

        return $message;
    }

    public function update(SupportTicket $ticket, array $data): void
    {
        $oldValues = $ticket->only(['status', 'priority', 'category', 'assigned_to', 'admin_note']);

        $ticket->fill($data);
        $ticket->resolved_at = $data['status'] === 'resolved' ? ($ticket->resolved_at ?? now()) : null;
        $ticket->closed_at = $data['status'] === 'closed' ? ($ticket->closed_at ?? now()) : null;
        $ticket->save();

        $this->audit('update', $oldValues, $ticket->only(['status', 'priority', 'category', 'assigned_to', 'admin_note']), $ticket);
    }

    public function delete(SupportTicket $ticket): void
    {
        $oldValues = $ticket->only(['ticket_code', 'subject', 'status', 'priority', 'category', 'assigned_to']);
        $ticket->delete();
        $this->audit('delete', $oldValues, [], $ticket);
    }

    public function statsFor(User $user): array
    {
        $query = SupportTicket::query();

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->getAuthIdentifier());
        }

        return [
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'urgent' => (clone $query)->where('priority', 'urgent')->count(),
        ];
    }

    public function assignees()
    {
        return User::query()->where('role', 'admin')->orderBy('name')->get(['id', 'name', 'email']);
    }

    public function canView(User $user, SupportTicket $ticket): bool
    {
        return $user->isAdmin() || (int) $ticket->user_id === (int) $user->getAuthIdentifier();
    }

    public function can(User $user, string $permission): bool
    {
        return method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission);
    }

    private function makeTicketCode(): string
    {
        do {
            $code = 'SP-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (SupportTicket::query()->where('ticket_code', $code)->exists());

        return $code;
    }

    private function storeAttachments(Request $request, SupportTicket $ticket, SupportTicketMessage $message): void
    {
        foreach ($request->file('attachments', []) as $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('support-tickets/'.$ticket->ticket_code, 'public');

            SupportTicketAttachment::query()->create([
                'support_ticket_id' => $ticket->id,
                'support_ticket_message_id' => $message->id,
                'user_id' => $request->user()?->getAuthIdentifier(),
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize() ?: 0,
            ]);
        }
    }

    private function audit(string $action, array $oldValues, array $newValues, SupportTicket $ticket): void
    {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->record(
            $action,
            'support',
            $oldValues,
            $newValues,
            ['ticket_code' => $ticket->ticket_code],
            $ticket
        );
    }
}
