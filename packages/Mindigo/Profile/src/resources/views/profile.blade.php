@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-profile::app.page_title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/Profile/src/resources/css/app.css',
        'packages/Mindigo/Profile/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="mx-auto flex max-w-7xl flex-col gap-6">
        @include('profile::partials._topbar')

        <div class="grid gap-6 xl:grid-cols-[20rem_minmax(0,1fr)]">
            @include('profile::partials._sidebar')

            <div class="min-w-0 space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-2xl">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-black ring-1 {{ $roleProfile['badge'] }}">
                                {{ $roleProfile['label'] }}
                            </span>
                            <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">@lang('Mindigo-profile::app.role_profile_title')</h2>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">{{ $roleProfile['summary'] }}</p>
                        </div>
                        <div class="grid w-full gap-2 sm:w-auto sm:grid-cols-3">
                            @foreach($roleProfile['items'] as $item)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <div class="text-xs font-black uppercase tracking-wide text-slate-400">@lang('Mindigo-profile::app.scope')</div>
                                    <div class="mt-1 text-sm font-black text-slate-800">{{ $item }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                @include('profile::partials._tab_profile')
                @include('profile::partials._tab_email')
                @include('profile::partials._tab_security')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.__profileSuccess = @json(session('success'));
        window.__profileErrors = @json($errors->all());
    </script>
@endsection
