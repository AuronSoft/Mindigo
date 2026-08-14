@extends('Mindigo-dashboard::layouts')
@section('title', ($deck->exists ? __('learning-tools::app.flashcards.edit') : __('learning-tools::app.flashcards.new')) . ' · Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection
@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">
    @include('learning-tools::partials.header', [
        'eyebrow' => __('learning-tools::app.flashcards.title'),
        'title' => $deck->exists ? __('learning-tools::app.flashcards.edit') : __('learning-tools::app.flashcards.new'),
        'subtitle' => __('learning-tools::app.flashcards.form_subtitle'),
    ])
    <div class="p-6">
        <form method="POST" action="{{ $deck->exists ? route('learning-tools.flashcards.update', $deck) : route('learning-tools.flashcards.store') }}" class="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf @if($deck->exists) @method('PUT') @endif
            <div class="space-y-5">
                <label class="block"><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.title')</span><input name="title" required maxlength="180" value="{{ old('title', $deck->title) }}" class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 text-sm font-bold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50"></label>
                <label class="block"><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.description')</span><textarea name="description" rows="4" maxlength="1000" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-green-400 focus:ring-4 focus:ring-green-50">{{ old('description', $deck->description) }}</textarea></label>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.subject')</span><select name="subject_id" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold"><option value="">@lang('learning-tools::app.flashcards.general')</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $deck->subject_id) == $subject->id)>{{ $subject->name }}</option>@endforeach</select></label>
                    <label><span class="text-sm font-black text-slate-700">@lang('learning-tools::app.fields.visibility')</span><select name="visibility" class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold"><option value="private" @selected(old('visibility', $deck->visibility ?: 'private') === 'private')>@lang('learning-tools::app.flashcards.visibility.private')</option><option value="public" @selected(old('visibility', $deck->visibility) === 'public')>@lang('learning-tools::app.flashcards.visibility.public')</option></select></label>
                </div>
                @if($classrooms->isNotEmpty())
                    <fieldset><legend class="text-sm font-black text-slate-700">@lang('learning-tools::app.flashcards.assign_classes')</legend><div class="mt-3 grid gap-3 sm:grid-cols-2">@foreach($classrooms as $classroom)<label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm font-bold text-slate-700"><input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}" @checked(in_array($classroom->id, old('classroom_ids', $deck->exists ? $deck->classrooms->pluck('id')->all() : []))) class="h-5 w-5 rounded text-green-600">{{ $classroom->name }}</label>@endforeach</div></fieldset>
                @endif
            </div>
            @if($errors->any())<p class="mt-4 text-sm font-bold text-red-600">{{ $errors->first() }}</p>@endif
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-5"><a href="{{ route('learning-tools.flashcards.index') }}" class="text-sm font-black text-slate-500 no-underline">@lang('learning-tools::app.actions.cancel')</a><button class="h-11 rounded-full bg-green-600 px-7 text-sm font-black text-white">@lang('learning-tools::app.actions.save')</button></div>
        </form>
    </div>
</div>
@endsection
