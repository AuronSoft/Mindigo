@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-system-setting::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/SystemSetting/src/resources/css/app.css',
        'packages/Mindigo/SystemSetting/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="mx-auto flex max-w-7xl flex-col gap-6">
        <header class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm shadow-slate-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-400">
                        <a href="{{ route('dashboard') }}" class="text-slate-500 no-underline transition hover:text-green-700">Dashboard</a>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        <span class="text-slate-700">@lang('Mindigo-system-setting::app.breadcrumb')</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">@lang('Mindigo-system-setting::app.heading')</h1>
                    <p class="mt-1 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                        @lang('Mindigo-system-setting::app.description')
                    </p>
                </div>
                <button type="submit" form="system-setting-form" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-green-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                    <svg class="h-4 w-4 fill-none stroke-current stroke-[2.5]" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @lang('Mindigo-system-setting::app.save')
                </button>
            </div>
        </header>

        <form id="system-setting-form" method="POST" action="{{ route('system-settings.update') }}" class="grid gap-6 xl:grid-cols-[17rem_minmax(0,1fr)]">
            @csrf
            @method('PUT')

            <aside class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm shadow-slate-200/70 xl:sticky xl:top-6 xl:self-start">
                <nav class="flex gap-2 overflow-x-auto xl:flex-col xl:overflow-visible">
                    @foreach($groups as $groupKey => $group)
                        <a href="#setting-{{ $groupKey }}" class="flex min-w-48 items-center gap-3 rounded-xl px-3 py-3 text-sm font-black text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700 xl:min-w-0">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-green-50 text-green-600">
                                <svg class="h-5 w-5 fill-none stroke-current stroke-2" viewBox="0 0 24 24"><path d="M12 3v18M3 12h18"/></svg>
                            </span>
                            <span class="truncate">{{ $group['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="min-w-0 space-y-6">
                @foreach($groups as $groupKey => $group)
                    <section id="setting-{{ $groupKey }}" class="scroll-mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                        <div class="mb-5 border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-black text-slate-950">{{ $group['label'] }}</h2>
                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">{{ $group['description'] }}</p>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            @foreach($group['settings'] as $setting)
                                @php($field = 'settings[' . $setting['key'] . ']')

                                <div class="{{ $setting['type'] === 'boolean' ? 'lg:col-span-2' : '' }}">
                                    @if($setting['type'] === 'boolean')
                                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-green-200 hover:bg-green-50/60">
                                            <span class="text-sm font-black text-slate-900">{{ $setting['label'] }}</span>
                                            <input type="hidden" name="{{ $field }}" value="0">
                                            <input type="checkbox" name="{{ $field }}" value="1" class="h-5 w-5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                                {{ old('settings.' . $setting['key'], $setting['value']) ? 'checked' : '' }}
                                                data-system-setting-field>
                                        </label>
                                    @elseif($setting['type'] === 'select')
                                        <label class="mb-1.5 block text-xs font-black text-slate-700" for="{{ $setting['key'] }}">{{ $setting['label'] }}</label>
                                        <select id="{{ $setting['key'] }}" name="{{ $field }}" class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition focus:border-green-400 focus:ring-4 focus:ring-green-100" data-system-setting-field>
                                            @foreach($setting['options'] as $value => $label)
                                                <option value="{{ $value }}" {{ old('settings.' . $setting['key'], $setting['value']) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <label class="mb-1.5 block text-xs font-black text-slate-700" for="{{ $setting['key'] }}">{{ $setting['label'] }}</label>
                                        <input id="{{ $setting['key'] }}" name="{{ $field }}" value="{{ old('settings.' . $setting['key'], $setting['value']) }}"
                                            type="{{ $setting['type'] === 'integer' ? 'number' : ($setting['type'] === 'email' ? 'email' : 'text') }}"
                                            min="{{ $setting['type'] === 'integer' ? 0 : '' }}"
                                            class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                                            data-system-setting-field>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        window.__systemSettingSuccess = @json(session('success'));
        window.__systemSettingInfo = @json(session('info'));
        window.__systemSettingErrors = @json($errors->all());
        window.__systemSettingMessages = @json(__('Mindigo-system-setting::app.messages'));
    </script>
@endsection
