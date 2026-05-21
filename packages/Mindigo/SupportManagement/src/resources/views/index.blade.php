@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-support-management::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/SupportManagement/src/resources/css/app.css',
        'packages/Mindigo/SupportManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="support-page mx-auto flex max-w-7xl flex-col gap-6">
        <header class="support-hero">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 text-xs font-black text-slate-400">
                    <a href="{{ route('dashboard') }}" class="text-slate-500 no-underline transition hover:text-green-700">@lang('Mindigo-dashboard::app.dashboard')</a>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="text-slate-700">@lang('Mindigo-support-management::app.breadcrumb')</span>
                </div>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">@lang('Mindigo-support-management::app.heading')</h1>
                <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-slate-500">@lang('Mindigo-support-management::app.description')</p>
            </div>
            <a href="{{ route('support-tickets.create') }}" class="support-primary-button">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                @lang('Mindigo-support-management::app.create_ticket')
            </a>
        </header>

        <section class="grid gap-4 md:grid-cols-4">
            @foreach(['open' => 'open_tickets', 'in_progress' => 'in_progress_tickets', 'resolved' => 'resolved_tickets', 'urgent' => 'urgent_tickets'] as $key => $label)
                <article class="support-stat-card">
                    <span>@lang('Mindigo-support-management::app.' . $label)</span>
                    <strong>{{ number_format($stats[$key] ?? 0) }}</strong>
                </article>
            @endforeach
        </section>

        <form method="GET" action="{{ route('support-tickets.index') }}" class="support-filter">
            <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="@lang('Mindigo-support-management::app.search_placeholder')" class="support-input">
            <select name="status" class="support-select">
                <option value="">@lang('Mindigo-support-management::app.status')</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>@lang('Mindigo-support-management::app.statuses.' . $status)</option>
                @endforeach
            </select>
            <select name="priority" class="support-select">
                <option value="">@lang('Mindigo-support-management::app.priority')</option>
                @foreach($priorities as $priority)
                    <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>@lang('Mindigo-support-management::app.priorities.' . $priority)</option>
                @endforeach
            </select>
            <select name="category" class="support-select">
                <option value="">@lang('Mindigo-support-management::app.category')</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>@lang('Mindigo-support-management::app.categories.' . $category)</option>
                @endforeach
            </select>
            <button type="submit" class="support-filter-button">@lang('Mindigo-support-management::app.filter')</button>
            <a href="{{ route('support-tickets.index') }}" class="support-reset-button">@lang('Mindigo-support-management::app.reset')</a>
        </form>

        <section class="support-card overflow-hidden">
            @if($tickets->count())
                <div class="overflow-x-auto">
                    <table class="support-table">
                        <thead>
                            <tr>
                                <th>@lang('Mindigo-support-management::app.ticket_code')</th>
                                <th>@lang('Mindigo-support-management::app.subject')</th>
                                <th>@lang('Mindigo-support-management::app.requester')</th>
                                <th>@lang('Mindigo-support-management::app.status')</th>
                                <th>@lang('Mindigo-support-management::app.priority')</th>
                                <th>@lang('Mindigo-support-management::app.updated_at')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td>
                                        <a href="{{ route('support-tickets.show', $ticket) }}" class="font-black text-green-700 no-underline hover:text-green-600">{{ $ticket->ticket_code }}</a>
                                    </td>
                                    <td>
                                        <span class="block max-w-md truncate text-sm font-black text-slate-900">{{ $ticket->subject }}</span>
                                        <span class="mt-1 block text-xs font-semibold text-slate-400">@lang('Mindigo-support-management::app.categories.' . $ticket->category) · {{ $ticket->messages_count }} @lang('Mindigo-support-management::app.reply')</span>
                                    </td>
                                    <td>
                                        <span class="block text-sm font-black text-slate-800">{{ $ticket->user_name }}</span>
                                        <span class="block text-xs font-semibold text-slate-400">{{ $ticket->user_email }}</span>
                                    </td>
                                    <td><span class="support-badge support-status-{{ $ticket->status }}">@lang('Mindigo-support-management::app.statuses.' . $ticket->status)</span></td>
                                    <td><span class="support-badge support-priority-{{ $ticket->priority }}">@lang('Mindigo-support-management::app.priorities.' . $ticket->priority)</span></td>
                                    <td class="text-sm font-bold text-slate-500">{{ $ticket->updated_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $tickets->links() }}</div>
            @else
                <div class="p-6">
                    @include('core::partials.empty-state', [
                        'title' => __('Mindigo-support-management::app.empty_title'),
                        'message' => __('Mindigo-support-management::app.empty_desc'),
                    ])
                </div>
            @endif
        </section>
    </div>
@endsection
