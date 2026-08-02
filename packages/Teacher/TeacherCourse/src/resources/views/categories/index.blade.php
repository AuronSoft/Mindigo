@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-course::categories.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
        <div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::categories.area')</p><h1 class="text-lg font-black text-slate-950">@lang('teacher-course::categories.title')</h1><p class="text-xs font-semibold text-slate-400">@lang('teacher-course::categories.subtitle')</p></div>
        @can('create', \Mindigo\TeacherCourse\Models\CourseCategory::class)<a href="{{ route('admin.course-categories.create') }}" class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-black text-white no-underline hover:bg-green-700">@lang('teacher-course::categories.create')</a>@endcan
    </header>
    <main class="space-y-4 p-4 sm:p-6">
        <form method="GET" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-[minmax(0,1fr)_13rem_auto]">
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="h-10 rounded-lg border border-slate-200 px-3 text-sm" placeholder="@lang('teacher-course::categories.search')">
            <select name="status" class="h-10 rounded-lg border border-slate-200 px-3 text-sm font-bold"><option value="">@lang('teacher-course::categories.all_statuses')</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>@lang('teacher-course::categories.active')</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>@lang('teacher-course::categories.inactive')</option></select>
            <button class="h-10 rounded-lg bg-green-600 px-5 text-sm font-black text-white">@lang('teacher-course::categories.filter')</button>
        </form>
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-170 text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400">
                        <tr>
                            <th class="px-5 py-3">@lang('teacher-course::categories.name')</th>
                            <th class="px-5 py-3">@lang('teacher-course::categories.order')</th>
                            <th class="px-5 py-3">@lang('teacher-course::categories.courses')</th>
                            <th class="px-5 py-3">@lang('teacher-course::categories.status')</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $category)
                            <tr>
                                <td class="px-5 py-3">
                                    <strong>{{ $category->name }}</strong>
                                    <span class="mt-1 block text-xs text-slate-400">{{ $category->description }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $category->sort_order }}</td>
                                <td class="px-5 py-3">{{ $category->courses_count }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $category->is_active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ __($category->is_active ? 'teacher-course::categories.active' : 'teacher-course::categories.inactive') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.course-categories.edit', $category) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-black text-slate-600 no-underline">
                                            @lang('teacher-course::categories.edit')
                                        </a>
                                        <form method="POST" action="{{ route('admin.course-categories.destroy', $category) }}" data-mindigo-confirm-title="@lang('teacher-course::categories.delete')" data-mindigo-confirm-message="@lang('teacher-course::categories.delete_confirm')" data-mindigo-confirm-text="@lang('teacher-course::categories.delete')" data-mindigo-confirm-cancel="@lang('teacher-course::categories.cancel')" data-mindigo-confirm-type="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg bg-red-50 px-3 py-2 text-xs font-black text-red-700">@lang('teacher-course::categories.delete')</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-sm font-semibold text-slate-400">
                                    @lang('teacher-course::categories.empty')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $categories->links() }}</div>
        </section>
    </main>
</div>
@endsection
