@extends('Mindigo-dashboard::layouts')

@section('title', __('teacher-onboarding::interview.title').' - '.$application->full_name)
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('admin.teacher-applications.show', $application) }}" class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500 no-underline hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-5 w-5" />
            </a>
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-onboarding::interview.area')</p>
                <h1 class="mt-0.5 truncate text-lg font-black text-slate-950">{{ $application->full_name }}</h1>
                <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-onboarding::interview.subtitle')</p>
            </div>
        </div>

        @if ($interview->result)
            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">
                @lang('teacher-onboarding::interview.results.'.$interview->result)
            </span>
        @endif
    </header>

    <main class="grid gap-5 p-4 sm:p-6 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <section class="space-y-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::interview.schedule_title')</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::interview.scheduled_at')</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $interview->scheduled_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::interview.mode')</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">@lang('teacher-onboarding::interview.modes.'.$interview->mode)</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::interview.interviewer')</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $interview->interviewer?->name ?? __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::interview.meeting_url')</dt>
                        <dd class="mt-1 truncate text-sm font-bold text-slate-800">{{ $interview->meeting_url ?: __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3 sm:col-span-2">
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::interview.pre_interview_note')</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm font-semibold leading-6 text-slate-700">{{ $interview->pre_interview_note ?: __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::interview.evaluation')</h2>
                <form method="POST" action="{{ route('admin.teacher-applications.interviews.evaluate', [$application, $interview]) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach (['subject_knowledge_score', 'pedagogy_score', 'communication_score', 'lms_technology_score'] as $field)
                            <label class="block space-y-2">
                                <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.'.$field)</span>
                                <input type="number" min="1" max="10" name="{{ $field }}" value="{{ old($field, $interview->{$field}) }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold">
                            </label>
                        @endforeach
                    </div>

                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.result')</span>
                        <select name="result" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold">
                            @foreach ($results as $result)
                                <option value="{{ $result }}" @selected(old('result', $interview->result) === $result)>@lang('teacher-onboarding::interview.results.'.$result)</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.overall_comment')</span>
                        <textarea name="overall_comment" rows="7" class="w-full rounded-2xl border border-slate-200 p-4 text-sm font-semibold">{{ old('overall_comment', $interview->overall_comment) }}</textarea>
                    </label>

                    <button class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-green-600 px-5 text-sm font-black text-white">
                        <x-heroicon-o-clipboard-document-check class="h-4 w-4" />
                        @lang('teacher-onboarding::interview.save_evaluation')
                    </button>
                </form>
            </article>
        </section>

        <aside class="space-y-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::interview.update_schedule')</h2>
                <form method="POST" action="{{ route('admin.teacher-applications.interviews.update', [$application, $interview]) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.scheduled_at')</span>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $interview->scheduled_at?->format('Y-m-d\TH:i')) }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold">
                    </label>

                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.mode')</span>
                        <select name="mode" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold">
                            @foreach ($modes as $mode)
                                <option value="{{ $mode }}" @selected(old('mode', $interview->mode) === $mode)>@lang('teacher-onboarding::interview.modes.'.$mode)</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.meeting_url')</span>
                        <input type="url" name="meeting_url" value="{{ old('meeting_url', $interview->meeting_url) }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold">
                    </label>

                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.pre_interview_note')</span>
                        <textarea name="pre_interview_note" rows="4" class="w-full rounded-2xl border border-slate-200 p-4 text-sm font-semibold">{{ old('pre_interview_note', $interview->pre_interview_note) }}</textarea>
                    </label>

                    <button class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 px-5 text-sm font-black text-slate-700">
                        <x-heroicon-o-calendar-days class="h-4 w-4" />
                        @lang('teacher-onboarding::interview.update_schedule')
                    </button>
                </form>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::admin.review_history')</h2>
                <dl class="mt-4 space-y-3">
                    <div>
                        <dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::interview.evaluator')</dt>
                        <dd class="text-sm font-bold text-slate-800">{{ $interview->evaluator?->name ?? __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::interview.evaluated_at')</dt>
                        <dd class="text-sm font-bold text-slate-800">{{ $interview->evaluated_at?->format('d/m/Y H:i') ?? __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::interview.result')</dt>
                        <dd class="text-sm font-bold text-slate-800">{{ $interview->result ? __('teacher-onboarding::interview.results.'.$interview->result) : __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                </dl>
            </article>
        </aside>
    </main>
</div>
@endsection
