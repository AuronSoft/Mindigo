@extends('Mindigo-dashboard::layouts')

@section('title', __('academic-calendar::app.exceptions_title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white px-6 py-4">
        <p class="text-[11px] font-black uppercase tracking-widest text-green-700">LMS ADMIN</p>
        <h1 class="text-lg font-black text-slate-950">@lang('academic-calendar::app.exceptions_title')</h1>
        <p class="text-xs font-semibold text-slate-400">@lang('academic-calendar::app.exceptions_subtitle')</p>
    </header>

    <main class="grid gap-5 p-4 sm:p-6 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <section class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-black text-slate-950">@lang('academic-calendar::app.create_exception')</h2>
            <form method="POST" action="{{ route('admin.calendar-exceptions.store') }}" class="mt-4 space-y-4" data-calendar-exception-form>
                @csrf
                <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('academic-calendar::app.scope')</span><select name="scope" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold" data-exception-scope><option value="global" @selected(old('scope', 'global') === 'global')>@lang('academic-calendar::app.scope_global')</option><option value="course" @selected(old('scope') === 'course')>@lang('academic-calendar::app.scope_course')</option></select></label>
                <label class="block" data-exception-course><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('academic-calendar::app.course')</span><select name="course_id" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold"><option value="">@lang('academic-calendar::app.all_courses')</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->name }}</option>@endforeach</select>@error('course_id')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror</label>
                <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('academic-calendar::app.date')</span><input type="date" name="exception_date" value="{{ old('exception_date') }}" required class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold">@error('exception_date')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror</label>
                <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('academic-calendar::app.title')</span><input name="title" value="{{ old('title') }}" maxlength="255" required class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold">@error('title')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror</label>
                <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('academic-calendar::app.reason')</span><textarea name="reason" rows="4" maxlength="1000" required class="w-full resize-none rounded-xl border border-slate-200 p-3 text-sm font-semibold">{{ old('reason') }}</textarea>@error('reason')<p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>@enderror</label>
                <button class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-green-600 text-sm font-black text-white hover:bg-green-700">@lang('academic-calendar::app.save')</button>
            </form>
        </section>

        <div class="min-w-0 space-y-4">
            <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
                <select name="scope" class="h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold"><option value="">@lang('academic-calendar::app.all_scopes')</option><option value="global" @selected(($filters['scope'] ?? '') === 'global')>@lang('academic-calendar::app.scope_global')</option><option value="course" @selected(($filters['scope'] ?? '') === 'course')>@lang('academic-calendar::app.scope_course')</option></select>
                <select name="course_id" class="h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold"><option value="">@lang('academic-calendar::app.all_courses')</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(($filters['course_id'] ?? null) == $course->id)>{{ $course->name }}</option>@endforeach</select>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="@lang('academic-calendar::app.from')" class="h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold">
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="@lang('academic-calendar::app.to')" class="h-10 rounded-xl border border-slate-200 px-3 text-sm font-bold">
                <div class="flex gap-2"><button class="flex-1 rounded-xl bg-green-600 px-3 text-xs font-black text-white">@lang('academic-calendar::app.filter')</button><a href="{{ route('admin.calendar-exceptions.index') }}" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 text-slate-500 no-underline" aria-label="@lang('academic-calendar::app.clear')"><x-heroicon-o-x-mark class="h-4 w-4" /></a></div>
            </form>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="divide-y divide-slate-100">
                @forelse($exceptions as $exception)
                    <article class="grid items-center gap-4 px-5 py-4 md:grid-cols-[7rem_minmax(0,1fr)_11rem_3rem]">
                        <div><p class="text-lg font-black text-slate-950">{{ $exception->exception_date->format('d/m') }}</p><p class="text-xs font-bold text-slate-400">{{ $exception->exception_date->format('Y') }}</p></div>
                        <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="truncate text-sm font-black text-slate-900">{{ $exception->title }}</h3><span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $exception->course_id ? 'bg-blue-50 text-blue-700' : 'bg-green-50 text-green-700' }}">{{ $exception->course?->name ?: __('academic-calendar::app.scope_global') }}</span></div><p class="mt-1 text-xs font-semibold text-slate-500">{{ $exception->reason }}</p></div>
                        <p class="text-xs font-semibold text-slate-400">@lang('academic-calendar::app.created_by')<br><span class="font-bold text-slate-600">{{ $exception->creator?->name ?: '—' }}</span></p>
                        <form method="POST" action="{{ route('admin.calendar-exceptions.destroy', $exception) }}" data-mindigo-confirm-title="@lang('academic-calendar::app.delete')" data-mindigo-confirm-message="@lang('academic-calendar::app.delete_confirm')" data-mindigo-confirm-text="@lang('academic-calendar::app.delete')" data-mindigo-confirm-type="danger">@csrf @method('DELETE')<button class="grid h-9 w-9 place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" aria-label="@lang('academic-calendar::app.delete')"><x-heroicon-o-trash class="h-4 w-4" /></button></form>
                    </article>
                @empty
                    <div class="px-6 py-16 text-center"><x-heroicon-o-calendar-days class="mx-auto h-10 w-10 text-slate-300" /><p class="mt-3 text-sm font-black text-slate-600">@lang('academic-calendar::app.empty')</p></div>
                @endforelse
            </div>@if($exceptions->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $exceptions->links() }}</div>@endif</section>
        </div>
    </main>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('[data-calendar-exception-form]').forEach(form=>{const scope=form.querySelector('[data-exception-scope]');const course=form.querySelector('[data-exception-course]');const sync=()=>course.hidden=scope.value!=='course';scope.addEventListener('change',sync);sync();}));</script>
@endsection
