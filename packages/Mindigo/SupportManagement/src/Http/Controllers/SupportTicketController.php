<?php

namespace Mindigo\SupportManagement\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mindigo\Auth\Models\User;
use Mindigo\SupportManagement\Http\Requests\SupportReplyRequest;
use Mindigo\SupportManagement\Http\Requests\SupportTicketRequest;
use Mindigo\SupportManagement\Http\Requests\SupportTicketUpdateRequest;
use Mindigo\SupportManagement\Models\SupportTicket;
use Mindigo\SupportManagement\Services\SupportTicketService;
use Symfony\Component\HttpFoundation\Response;

class SupportTicketController extends Controller
{
    public function __construct(private SupportTicketService $tickets)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorizePermission($user, 'support-tickets.view');

        return view('Mindigo-support-management::index', [
            'tickets' => $this->tickets->filteredList($user, $request->only(['keyword', 'status', 'priority', 'category'])),
            'stats' => $this->tickets->statsFor($user),
            'assignees' => $this->tickets->assignees(),
            'statuses' => SupportTicket::STATUSES,
            'priorities' => SupportTicket::PRIORITIES,
            'categories' => SupportTicket::CATEGORIES,
            'filters' => $request->only(['keyword', 'status', 'priority', 'category']),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizePermission($request->user(), 'support-tickets.create');

        return view('Mindigo-support-management::create', [
            'priorities' => SupportTicket::PRIORITIES,
            'categories' => SupportTicket::CATEGORIES,
        ]);
    }

    public function store(SupportTicketRequest $request): RedirectResponse
    {
        $ticket = $this->tickets->create($request);

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

        return view('Mindigo-support-management::show', [
            'ticket' => $supportTicket,
            'assignees' => $this->tickets->assignees(),
            'statuses' => SupportTicket::STATUSES,
            'priorities' => SupportTicket::PRIORITIES,
            'categories' => SupportTicket::CATEGORIES,
        ]);
    }

    public function reply(SupportReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorizeTicket($request, $supportTicket);
        $this->tickets->reply($request, $supportTicket);

        return back()->with('success', __('Mindigo-support-management::app.messages.replied'));
    }

    public function update(SupportTicketUpdateRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $this->tickets->update($supportTicket, $request->validated());

        return back()->with('success', __('Mindigo-support-management::app.messages.updated'));
    }

    public function destroy(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        if (!$request->user()->isAdmin()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $this->authorizePermission($request->user(), 'support-tickets.delete');
        $this->tickets->delete($supportTicket);

        return redirect()
            ->route('support-tickets.index')
            ->with('success', __('Mindigo-support-management::app.messages.deleted'));
    }

    private function authorizeTicket(Request $request, SupportTicket $ticket): void
    {
        if ($this->tickets->canView($request->user(), $ticket)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }

    private function authorizePermission(User $user, string $permission): void
    {
        if ($this->tickets->can($user, $permission)) {
            return;
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}
