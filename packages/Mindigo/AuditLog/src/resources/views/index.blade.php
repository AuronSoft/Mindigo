@extends('Mindigo-dashboard::layouts')

@section('title', 'Nhật ký thao tác - Mindigo')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/AuditLog/src/resources/css/app.css',
        'packages/Mindigo/AuditLog/src/resources/js/app.js',
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
                        <span class="text-slate-700">Nhật ký thao tác</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Nhật ký thao tác</h1>
                    <p class="mt-1 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                        Theo dõi các hành động quan trọng như đăng nhập, đăng xuất và thay đổi cấu hình hệ thống.
                    </p>
                </div>
            </div>
        </header>

        <form method="GET" action="{{ route('audit-logs.index') }}" class="audit-log-card p-4" data-audit-log-filter>
            <div class="grid gap-3 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_10rem_10rem_auto] lg:items-end">
                <div>
                    <label for="keyword" class="mb-1.5 block text-xs font-black text-slate-700">Tìm kiếm</label>
                    <input id="keyword" name="keyword" value="{{ request('keyword') }}" placeholder="User, email, module, action..."
                        class="audit-log-input">
                </div>
                <div>
                    <label for="module" class="mb-1.5 block text-xs font-black text-slate-700">Module</label>
                    <select id="module" name="module" class="audit-log-input">
                        <option value="">Tất cả</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="action" class="mb-1.5 block text-xs font-black text-slate-700">Hành động</label>
                    <select id="action" name="action" class="audit-log-input">
                        <option value="">Tất cả</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="mb-1.5 block text-xs font-black text-slate-700">Từ ngày</label>
                    <input id="date_from" name="date_from" value="{{ request('date_from') }}" type="date"
                        class="audit-log-input px-3">
                </div>
                <div>
                    <label for="date_to" class="mb-1.5 block text-xs font-black text-slate-700">Đến ngày</label>
                    <input id="date_to" name="date_to" value="{{ request('date_to') }}" type="date"
                        class="audit-log-input px-3">
                </div>
                <div class="flex gap-2">
                    <button class="inline-flex min-h-11 items-center justify-center rounded-xl bg-green-600 px-4 text-sm font-black text-white transition hover:bg-green-500" type="submit">Lọc</button>
                    <a href="{{ route('audit-logs.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-black text-slate-600 no-underline transition hover:bg-slate-50">Xóa</a>
                </div>
            </div>
        </form>

        <section class="audit-log-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Thời gian</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Người dùng</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Module</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Hành động</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase text-slate-500">Request</th>
                            <th class="px-4 py-3 text-right text-xs font-black uppercase text-slate-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="transition hover:bg-green-50/40">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-slate-600">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-black text-slate-900">{{ $log->user_name ?? 'Khách' }}</div>
                                    <div class="text-xs font-semibold text-slate-500">{{ $log->user_email ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">{{ $log->module }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-black text-green-700">{{ $log->action }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-500">
                                    <div>{{ $log->method ?? '-' }}</div>
                                    <div class="max-w-xs truncate text-xs">{{ $log->route_name ?? $log->url ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a href="{{ route('audit-logs.show', $log) }}" class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-200 px-3 text-xs font-black text-slate-600 no-underline transition hover:border-green-200 hover:bg-green-50 hover:text-green-700">Chi tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10">
                                    @include('core::partials.empty-state', [
                                        'preset' => request()->hasAny(['keyword', 'module', 'action', 'date_from', 'date_to']) ? 'search' : 'audit_logs',
                                    ])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $logs->links() }}
            </div>
        </section>
    </div>
@endsection
