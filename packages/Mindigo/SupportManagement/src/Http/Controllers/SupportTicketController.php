<?php

namespace Mindigo\SupportManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mindigo\Auth\Models\User;
use Mindigo\SupportManagement\Models\SupportTicket;
use Mindigo\SupportManagement\Models\SupportTicketAttachment;
use Mindigo\SupportManagement\Models\SupportTicketMessage;
use Symfony\Component\HttpFoundation\Response;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorizePermission($user, 'support-tickets.view');

        $query = SupportTicket::query()
            ->with(['requester:id,name,email,role', 'assignee:id,name,email,role'])
            ->withCount('messages')
            ->latest('updated_at');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->getAuthIdentifier());
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));
            $query->where(function ($builder) use ($keyword) {
                $builder->where('ticket_code', 'like', "%{$keyword}%")
                    ->orWhere('subject', 'like', "%{$keyword}%")
                    ->orWhere('user_name', 'like', "%{$keyword}%")
                    ->orWhere('user_email', 'like', "%{$keyword}%");
            });
        }

        foreach (['status', 'priority', 'category'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $tickets = $query->paginate(12)->withQueryString();
        $stats = $this->statsFor($user);
        $assignees = User::query()->where('role', 'admin')->orderBy('name')->get(['id', 'name', 'email']);

        return view('Mindigo-support-management::index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'assignees' => $assignees,
            'statuses' => SupportTicket::STATUSES,
            'priorities' => SupportTicket::PRIORITIES,
            'categories' => SupportTicket::CATEGORIES,
            'filters' => $request->only(['keyword', 'status', 'priority', 'category']),
        ]);
    }

    public function create()
    {
        $this->authorizePermission(request()->user(), 'support-tickets.create');

        return view('Mindigo-support-management::create', [
            'priorities' => SupportTicket::PRIORITIES,
            'categories' => SupportTicket::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'support-tickets.create');

        $validated = $request->validate([
            'category' => ['required', 'in:' . implode(',', SupportTicket::CATEGORIES)],
            'priority' => ['required', 'in:' . implode(',', SupportTicket::PRIORITIES)],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,txt,doc,docx'],
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $validated, $user) {
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

        return redirect()
            ->route('support-tickets.show', $ticket)
            ->with('success', __('Mindigo-support-management::app.messages.created'));
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        $this->authorizeTicket($request, $supportTicket);

        $supportTicket->load([
            'requester:id,name,email,role',
            'assignee:id,name,email,role',
            'messages.user:id,name,email,role',
            'messages.attachments',
            'attachments',
        ]);

        $assignees = User::query()->where('role', 'admin')->orderBy('name')->get(['id', 'name', 'email']);

        return view('Mindigo-support-management::show', [
            'ticket' => $supportTicket,
            'assignees' => $assignees,
            'statuses' => SupportTicket::STATUSES,
            'priorities' => SupportTicket::PRIORITIES,
            'categories' => SupportTicket::CATEGORIES,
        ]);
    }

    public function reply(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorizePermission($request->user(), 'support-tickets.reply');
        $this->authorizeTicket($request, $supportTicket);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
            'attachments.*' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,txt,doc,docx'],
        ]);

        $user = $request->user();
        $isInternal = $user->isAdmin() && $request->boolean('is_internal');

        $message = DB::transaction(function () use ($request, $supportTicket, $validated, $user, $isInternal) {
            $message = $supportTicket->messages()->create([
                'user_id' => $user->getAuthIdentifier(),
                'user_name' => $user->name,
                'user_email' => $user->email,
                'sender_role' => $user->role,
                'message' => $validated['message'],
                'is_internal' => $isInternal,
            ]);

            $supportTicket->forceFill([
                'last_replied_at' => now(),
                'status' => $supportTicket->status === 'open' && $user->isAdmin() ? 'in_progress' : $supportTicket->status,
            ])->save();

            $this->storeAttachments($request, $supportTicket, $message);

            return $message;
        });

        $this->audit('reply', [], ['message_id' => $message->id, 'is_internal' => $isInternal], $supportTicket);

        return back()->with('success', __('Mindigo-support-management::app.messages.replied'));
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        if (!$request->user()->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN);
        }
        $this->authorizePermission($request->user(), 'support-tickets.manage');

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', SupportTicket::STATUSES)],
            'priority' => ['required', 'in:' . implode(',', SupportTicket::PRIORITIES)],
            'category' => ['required', 'in:' . implode(',', SupportTicket::CATEGORIES)],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'admin_note' => ['nullable', 'string', 'max:3000'],
        ]);

        $oldValues = $supportTicket->only(['status', 'priority', 'category', 'assigned_to', 'admin_note']);

        $supportTicket->fill($validated);
        $supportTicket->resolved_at = $validated['status'] === 'resolved' ? ($supportTicket->resolved_at ?? now()) : null;
        $supportTicket->closed_at = $validated['status'] === 'closed' ? ($supportTicket->closed_at ?? now()) : null;
        $supportTicket->save();

        $this->audit('update', $oldValues, $supportTicket->only(['status', 'priority', 'category', 'assigned_to', 'admin_note']), $supportTicket);

        return back()->with('success', __('Mindigo-support-management::app.messages.updated'));
    }

    private function statsFor(User $user): array
    {
        $query = SupportTicket::query();

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->getAuthIdentifier());
        }

        return [
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'urgent' => (clone $query)->where('priority', 'urgent')->count(),
        ];
    }

    private function authorizeTicket(Request $request, SupportTicket $ticket): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if ((int) $ticket->user_id !== (int) $user->getAuthIdentifier()) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function makeTicketCode(): string
    {
        do {
            $code = 'SP-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (SupportTicket::query()->where('ticket_code', $code)->exists());

        return $code;
    }

    private function storeAttachments(Request $request, SupportTicket $ticket, SupportTicketMessage $message): void
    {
        foreach ($request->file('attachments', []) as $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store('support-tickets/' . $ticket->ticket_code, 'public');

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
        if (!class_exists(\Mindigo\AuditLog\Services\AuditLogService::class)) {
            return;
        }

        app(\Mindigo\AuditLog\Services\AuditLogService::class)->record(
            $action,
            'support',
            $oldValues,
            $newValues,
            ['ticket_code' => $ticket->ticket_code],
            $ticket
        );
    }
}
