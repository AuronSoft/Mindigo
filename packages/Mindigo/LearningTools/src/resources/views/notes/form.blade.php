@extends('Mindigo-dashboard::layouts')
@section('title', ($note->exists ? __('learning-tools::app.notes.edit') : __('learning-tools::app.notes.new')) . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', [
        'eyebrow' => __('learning-tools::app.notes.title'),
        'title' => $note->exists ? __('learning-tools::app.notes.edit') : __('learning-tools::app.notes.new'),
        'subtitle' => __('learning-tools::app.notes.form_subtitle'),
    ])
    <div class="p-6">
        <form method="POST" action="{{ $note->exists ? route('learning-tools.notes.update', $note) : route('learning-tools.notes.store') }}" class="mx-auto max-w-4xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf @if($note->exists) @method('PUT') @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <label class="sm:col-span-2">
                    <span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.title')</span>
                    <input name="title" value="{{ old('title', $note->title) }}" required maxlength="180" class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">
                </label>
                <label>
                    <span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.subject')</span>
                    <select name="subject_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold">
                        <option value="">@lang('learning-tools::app.notes.no_subject')</option>
                        @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $note->subject_id) == $subject->id)>{{ $subject->name }}</option>@endforeach
                    </select>
                </label>
                <label>
                    <span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.topic')</span>
                    <select name="subject_topic_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold">
                        <option value="">@lang('learning-tools::app.fields.no_topic')</option>
                        @foreach($subjects as $subject)
                            @foreach($subject->topics as $topic)<option value="{{ $topic->id }}" @selected(old('subject_topic_id', $note->subject_topic_id) == $topic->id)>{{ $subject->name }} · {{ $topic->name }}</option>@endforeach
                        @endforeach
                    </select>
                </label>
                <label class="sm:col-span-2">
                    <span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.content')</span>
                    <textarea name="content" rows="14" maxlength="50000" class="mt-2 w-full resize-y rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold leading-6 outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">{{ old('content', $note->content) }}</textarea>
                </label>
                <label class="inline-flex items-center gap-3 text-sm font-black text-slate-700">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $note->is_pinned)) class="h-5 w-5 rounded border-slate-300 text-green-600">
                    @lang('learning-tools::app.notes.pin')
                </label>
            </div>
            @if($errors->any())<p class="mt-4 text-sm font-bold text-red-600">{{ $errors->first() }}</p>@endif
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5">
                <a href="{{ route('learning-tools.notes.index') }}" class="text-sm font-black text-slate-500 no-underline hover:text-slate-800">@lang('learning-tools::app.actions.cancel')</a>
                <button class="h-11 rounded-full bg-green-600 px-7 text-sm font-black text-white shadow-sm shadow-green-200 hover:bg-green-500">@lang('learning-tools::app.actions.save')</button>
            </div>
        </form>
        @if($note->exists)
            <form method="POST" action="{{ route('learning-tools.notes.destroy', $note) }}" class="mx-auto mt-4 max-w-4xl text-right">
                @csrf @method('DELETE')
                <button class="text-sm font-black text-red-600 hover:text-red-500">@lang('learning-tools::app.actions.delete')</button>
            </form>
        @endif
    </div>
</div>
@endsection
