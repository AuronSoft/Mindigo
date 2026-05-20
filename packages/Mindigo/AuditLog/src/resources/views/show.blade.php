@extends('Mindigo-dashboard::layouts')

@section('title', 'Chi tiết nhật ký - Mindigo')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/AuditLog/src/resources/css/app.css',
        'packages/Mindigo/AuditLog/src/resources/js/app.js',
    ])
@endsection

@php
    $formatJson = fn ($value) => json_encode($value ?: new stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

@section('content')
    <div class="mx-auto flex max-w-6xl flex-col gap-6">
        <header class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm shadow-slate-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-1.5 text-xs font-black text-slate-400">
                        <a href="{{ route('dashboard') }}" class="text-slate-500 no-underline transition hover:text-green-700">Dashboard</a>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        <a href="{{ route('audit-logs.index') }}" class="text-slate-500 no-underline transition hover:text-green-700">Nhật ký thao tác</a>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        <span class="text-slate-700">#{{ $log->id }}</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Chi tiết nhật ký #{{ $log->id }}</h1>
                    <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">{{ $log->created_at?->format('d/m/Y H:i:s') }}</p>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">Quay lại</a>
            </div>
        </header>

        <section class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                <div class="text-xs font-black uppercase text-slate-400">Người dùng</div>
                <div class="mt-2 text-base font-black text-slate-950">{{ $log->user_name ?? 'Khách' }}</div>
                <div class="mt-1 text-sm font-semibold text-slate-500">{{ $log->user_email ?? '-' }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                <div class="text-xs font-black uppercase text-slate-400">Hành động</div>
                <div class="mt-2 flex gap-2">
                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">{{ $log->module }}</span>
                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-black text-green-700">{{ $log->action }}</span>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                <div class="text-xs font-black uppercase text-slate-400">Request</div>
                <div class="mt-2 text-sm font-black text-slate-950">{{ $log->method ?? '-' }}</div>
                <div class="mt-1 truncate text-sm font-semibold text-slate-500">{{ $log->route_name ?? $log->url ?? '-' }}</div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-black text-slate-950">Dữ liệu cũ</h2>
                    <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-black text-slate-500 transition hover:bg-slate-50 hover:text-green-700" data-audit-copy="#audit-old-values">Copy</button>
                </div>
                <pre id="audit-old-values" class="audit-log-json mt-3" data-audit-json="old">{{ $formatJson($log->old_values) }}</pre>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-black text-slate-950">Dữ liệu mới</h2>
                    <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-black text-slate-500 transition hover:bg-slate-50 hover:text-green-700" data-audit-copy="#audit-new-values">Copy</button>
                </div>
                <pre id="audit-new-values" class="audit-log-json mt-3" data-audit-json="new">{{ $formatJson($log->new_values) }}</pre>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-black text-slate-950">Metadata</h2>
                <button type="button" class="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-black text-slate-500 transition hover:bg-slate-50 hover:text-green-700" data-audit-copy="#audit-metadata">Copy</button>
            </div>
            <dl class="mt-3 grid gap-3 text-sm lg:grid-cols-2">
                <div>
                    <dt class="font-black text-slate-500">IP</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $log->ip_address ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-black text-slate-500">User agent</dt>
                    <dd class="mt-1 break-all font-semibold text-slate-900">{{ $log->user_agent ?? '-' }}</dd>
                </div>
                <div class="lg:col-span-2">
                    <dt class="font-black text-slate-500">URL</dt>
                    <dd class="mt-1 break-all font-semibold text-slate-900">{{ $log->url ?? '-' }}</dd>
                </div>
            </dl>
            <pre id="audit-metadata" class="audit-log-json mt-4 max-h-80">{{ $formatJson($log->metadata) }}</pre>
        </section>
    </div>
@endsection
