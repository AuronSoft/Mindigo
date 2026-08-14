@extends('Mindigo-dashboard::layouts')
@section('title', ($plan->exists ? __('learning-tools::app.plans.edit') : __('learning-tools::app.plans.new')) . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection
@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', ['eyebrow' => __('learning-tools::app.plans.title'), 'title' => $plan->exists ? __('learning-tools::app.plans.edit') : __('learning-tools::app.plans.new'), 'subtitle' => __('learning-tools::app.plans.form_subtitle')])
    <div class="p-6">
        <form method="POST" action="{{ $plan->exists ? route('learning-tools.plans.update', $plan) : route('learning-tools.plans.store') }}" class="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf @if($plan->exists) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="sm:col-span-2"><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.title')</span><input name="title" required maxlength="180" value="{{ old('title', $plan->title) }}" class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50"></label>
                <label class="sm:col-span-2"><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.description')</span><textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold">{{ old('description', $plan->description) }}</textarea></label>
                <label><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.subject')</span><select name="subject_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold"><option value="">@lang('learning-tools::app.plans.general')</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $plan->subject_id) == $subject->id)>{{ $subject->name }}</option>@endforeach</select></label>
                <label><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.plans.scope')</span><select name="classroom_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold"><option value="">@lang('learning-tools::app.plans.personal')</option>@foreach($classrooms as $classroom)<option value="{{ $classroom->id }}" @selected(old('classroom_id', $plan->classroom_id) == $classroom->id)>{{ $classroom->name }}</option>@endforeach</select></label>
                <label><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.plans.start_date')</span><input type="date" name="start_date" required value="{{ old('start_date', $plan->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold"></label>
                <label><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.plans.end_date')</span><input type="date" name="end_date" required value="{{ old('end_date', $plan->end_date?->format('Y-m-d') ?? now()->addWeek()->format('Y-m-d')) }}" class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold"></label>
                <label><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.status')</span><select name="status" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold">@foreach(['active', 'completed', 'archived'] as $status)<option value="{{ $status }}" @selected(old('status', $plan->status ?: 'active') === $status)>@lang('learning-tools::app.statuses.' . $status)</option>@endforeach</select></label>
            </div>
            @if($errors->any())<p class="mt-4 text-sm font-bold text-red-600">{{ $errors->first() }}</p>@endif
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5"><a href="{{ route('learning-tools.plans.index') }}" class="text-sm font-black text-slate-500 no-underline">@lang('learning-tools::app.actions.cancel')</a><button class="h-11 rounded-full bg-green-600 px-7 text-sm font-black text-white">@lang('learning-tools::app.actions.save')</button></div>
        </form>
    </div>
</div>
@endsection
