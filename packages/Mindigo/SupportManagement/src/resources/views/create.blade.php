@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-support-management::app.new_ticket'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/SupportManagement/src/resources/css/app.css',
        'packages/Mindigo/SupportManagement/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="support-page mx-auto flex max-w-4xl flex-col gap-6">
        <header class="support-hero">
            <div>
                <div class="flex items-center gap-1.5 text-xs font-black text-slate-400">
                    <a href="{{ route('support-tickets.index') }}" class="text-slate-500 no-underline transition hover:text-green-700">@lang('Mindigo-support-management::app.tickets')</a>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    <span class="text-slate-700">@lang('Mindigo-support-management::app.new_ticket')</span>
                </div>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">@lang('Mindigo-support-management::app.new_ticket')</h1>
                <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">@lang('Mindigo-support-management::app.new_ticket_desc')</p>
            </div>
        </header>

        <form method="POST" action="{{ route('support-tickets.store') }}" enctype="multipart/form-data" class="support-card p-5">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <label class="support-field md:col-span-2">
                    <span>@lang('Mindigo-support-management::app.subject')</span>
                    <input name="subject" value="{{ old('subject') }}" class="support-input" required>
                    @error('subject')<strong>{{ $message }}</strong>@enderror
                </label>
                <label class="support-field">
                    <span>@lang('Mindigo-support-management::app.category')</span>
                    <select name="category" class="support-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(old('category', 'technical') === $category)>@lang('Mindigo-support-management::app.categories.' . $category)</option>
                        @endforeach
                    </select>
                </label>
                <label class="support-field">
                    <span>@lang('Mindigo-support-management::app.priority')</span>
                    <select name="priority" class="support-select" required>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>@lang('Mindigo-support-management::app.priorities.' . $priority)</option>
                        @endforeach
                    </select>
                </label>
                <label class="support-field md:col-span-2">
                    <span>@lang('Mindigo-support-management::app.message')</span>
                    <textarea name="message" rows="7" class="support-textarea" required>{{ old('message') }}</textarea>
                    @error('message')<strong>{{ $message }}</strong>@enderror
                </label>
                <label class="support-field md:col-span-2">
                    <span>@lang('Mindigo-support-management::app.attachments')</span>
                    <input type="file" name="attachments[]" multiple class="support-file">
                </label>
            </div>
            <div class="mt-5 flex justify-end">
                <button type="submit" class="support-primary-button">@lang('Mindigo-support-management::app.submit_ticket')</button>
            </div>
        </form>
    </div>
@endsection
