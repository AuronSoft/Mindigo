@extends('Mindigo-dashboard::layouts')

@php($editing = isset($category))
@section('title', __($editing ? 'teacher-course::categories.edit_title' : 'teacher-course::categories.create_title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center gap-4 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur"><a href="{{ route('admin.course-categories.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 text-slate-600"><x-heroicon-o-arrow-left class="h-4 w-4" /></a><div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::categories.area')</p><h1 class="text-lg font-black">{{ __($editing ? 'teacher-course::categories.edit_title' : 'teacher-course::categories.create_title') }}</h1><p class="text-xs font-semibold text-slate-400">@lang('teacher-course::categories.form_subtitle')</p></div></header>
    <main class="p-4 sm:p-6"><form method="POST" action="{{ $editing ? route('admin.course-categories.update', $category) : route('admin.course-categories.store') }}" class="mx-auto max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">@csrf @if($editing) @method('PUT') @endif
        <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::categories.name')</span><input name="name" required value="{{ old('name', $category->name ?? '') }}" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold">@error('name')<span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span>@enderror</label>
        <div class="grid gap-4 sm:grid-cols-2"><label><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::categories.status')</span><select name="is_active" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold"><option value="1" @selected((string) old('is_active', isset($category) ? (int) $category->is_active : 1) === '1')>@lang('teacher-course::categories.active')</option><option value="0" @selected((string) old('is_active', isset($category) ? (int) $category->is_active : 1) === '0')>@lang('teacher-course::categories.inactive')</option></select></label><label><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::categories.order')</span><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm font-bold"></label></div>
        <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::categories.description')</span><textarea name="description" rows="5" class="w-full rounded-xl border border-slate-200 p-3 text-sm">{{ old('description', $category->description ?? '') }}</textarea></label>
        <div class="flex justify-end gap-2"><a href="{{ route('admin.course-categories.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-black text-slate-600 no-underline">@lang('teacher-course::categories.cancel')</a><button class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-black text-white">@lang('teacher-course::categories.save')</button></div>
    </form></main>
</div>
@endsection
