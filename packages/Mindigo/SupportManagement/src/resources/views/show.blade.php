@extends('Mindigo-dashboard::layouts')

@section('title', $ticket->ticket_code)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/SupportManagement/src/resources/css/app.css',
        'packages/Mindigo/SupportManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="support-page mx-auto grid max-w-7xl gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-6">
            <header class="support-hero">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-400">
                        <a href="{{ route('support-tickets.index') }}" class="text-slate-500 no-underline transition hover:text-green-700">@lang('Mindigo-support-management::app.tickets')</a>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        <span class="text-slate-700">{{ $ticket->ticket_code }}</span>
                    </div>
                    <h1 class="mt-2 truncate text-2xl font-black tracking-tight text-slate-950">{{ $ticket->subject }}</h1>
                    <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">{{ $ticket->user_name }} · {{ $ticket->created_at?->diffForHumans() }}</p>
                </div>
                <span class="support-badge support-status-{{ $ticket->status }}">@lang('Mindigo-support-management::app.statuses.' . $ticket->status)</span>
            </header>

            @if(session('success'))
                <div class="support-alert">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <section class="support-card p-5">
                <h2 class="text-lg font-black text-slate-950">@lang('Mindigo-support-management::app.conversation')</h2>
                <div class="mt-5 space-y-4">
                    @foreach($ticket->messages as $message)
                        @if(!$message->is_internal || auth()->user()?->isAdmin())
                            <article class="support-message {{ $message->is_internal ? 'support-message-internal' : '' }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <strong class="block text-sm font-black text-slate-900">{{ $message->user_name }}</strong>
                                        <span class="text-xs font-bold text-slate-400">{{ $message->sender_role ?? '-' }} · {{ $message->created_at?->diffForHumans() }}</span>
                                    </div>
                                    @if($message->is_internal)
                                        <span class="support-badge support-priority-high">@lang('Mindigo-support-management::app.internal_note')</span>
                                    @endif
                                </div>
                                <p class="mt-3 whitespace-pre-wrap text-sm font-semibold leading-6 text-slate-600">{{ $message->message }}</p>
                                @if($message->attachments->count())
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($message->attachments as $attachment)
                                            <a href="{{ $attachment->url() }}" target="_blank" class="support-attachment">{{ $attachment->original_name }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endif
                    @endforeach
                </div>
            </section>

            @if(!$ticket->isClosed())
                <form method="POST" action="{{ route('support-tickets.reply', $ticket) }}" enctype="multipart/form-data" class="support-card p-5">
                    @csrf
                    <h2 class="text-lg font-black text-slate-950">@lang('Mindigo-support-management::app.reply')</h2>
                    <div class="mt-4 space-y-4">
                        <label class="support-field">
                            <span>@lang('Mindigo-support-management::app.message')</span>
                            <textarea name="message" rows="5" class="support-textarea" required>{{ old('message') }}</textarea>
                            @error('message')<strong>{{ $message }}</strong>@enderror
                        </label>
                        <label class="support-field">
                            <span>@lang('Mindigo-support-management::app.attachments')</span>
                            <input type="file" name="attachments[]" multiple class="support-file">
                        </label>
                        @if(auth()->user()?->isAdmin())
                            <label class="inline-flex items-center gap-2 text-sm font-black text-slate-600">
                                <input type="checkbox" name="is_internal" value="1" class="h-4 w-4 rounded border-slate-300 text-green-600">
                                @lang('Mindigo-support-management::app.internal_note')
                            </label>
                        @endif
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" class="support-primary-button">@lang('Mindigo-support-management::app.send_reply')</button>
                    </div>
                </form>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="support-card p-5">
                <h2 class="text-lg font-black text-slate-950">@lang('Mindigo-support-management::app.ticket_details')</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="support-detail-row"><dt>@lang('Mindigo-support-management::app.ticket_code')</dt><dd>{{ $ticket->ticket_code }}</dd></div>
                    <div class="support-detail-row"><dt>@lang('Mindigo-support-management::app.category')</dt><dd>@lang('Mindigo-support-management::app.categories.' . $ticket->category)</dd></div>
                    <div class="support-detail-row"><dt>@lang('Mindigo-support-management::app.priority')</dt><dd>@lang('Mindigo-support-management::app.priorities.' . $ticket->priority)</dd></div>
                    <div class="support-detail-row"><dt>@lang('Mindigo-support-management::app.assignee')</dt><dd>{{ $ticket->assignee?->name ?? __('Mindigo-support-management::app.unassigned') }}</dd></div>
                    <div class="support-detail-row"><dt>@lang('Mindigo-support-management::app.updated_at')</dt><dd>{{ $ticket->updated_at?->format('d/m/Y H:i') }}</dd></div>
                </dl>
            </section>

            @if(auth()->user()?->isAdmin())
                <form method="POST" action="{{ route('support-tickets.update', $ticket) }}" class="support-card p-5">
                    @csrf
                    @method('PUT')
                    <h2 class="text-lg font-black text-slate-950">@lang('Mindigo-support-management::app.management')</h2>
                    <div class="mt-4 space-y-4">
                        <label class="support-field">
                            <span>@lang('Mindigo-support-management::app.status')</span>
                            <select name="status" class="support-select">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($ticket->status === $status)>@lang('Mindigo-support-management::app.statuses.' . $status)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="support-field">
                            <span>@lang('Mindigo-support-management::app.priority')</span>
                            <select name="priority" class="support-select">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority }}" @selected($ticket->priority === $priority)>@lang('Mindigo-support-management::app.priorities.' . $priority)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="support-field">
                            <span>@lang('Mindigo-support-management::app.category')</span>
                            <select name="category" class="support-select">
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected($ticket->category === $category)>@lang('Mindigo-support-management::app.categories.' . $category)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="support-field">
                            <span>@lang('Mindigo-support-management::app.assignee')</span>
                            <select name="assigned_to" class="support-select">
                                <option value="">@lang('Mindigo-support-management::app.unassigned')</option>
                                @foreach($assignees as $assignee)
                                    <option value="{{ $assignee->id }}" @selected((int) $ticket->assigned_to === (int) $assignee->id)>{{ $assignee->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="support-field">
                            <span>@lang('Mindigo-support-management::app.admin_note')</span>
                            <textarea name="admin_note" rows="4" class="support-textarea">{{ old('admin_note', $ticket->admin_note) }}</textarea>
                        </label>
                    </div>
                    <button type="submit" class="support-primary-button mt-5 w-full">@lang('Mindigo-support-management::app.save_changes')</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
