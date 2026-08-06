@extends('Mindigo-dashboard::layouts')

@section('title', $application->full_name.' - '.__('teacher-onboarding::admin.title'))
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('admin.teacher-applications.index') }}" class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-slate-200 text-slate-500 no-underline hover:text-green-700"><x-heroicon-o-arrow-left class="h-5 w-5" /></a>
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-onboarding::admin.area')</p>
                <h1 class="mt-0.5 truncate text-lg font-black text-slate-950">{{ $application->full_name }}</h1>
                <p class="mt-1 text-xs font-semibold text-slate-400">{{ $application->application_code }} &middot; @lang('teacher-onboarding::admin.statuses.'.$application->status)</p>
            </div>
        </div>
        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">@lang('teacher-onboarding::application.types.'.$application->application_type)</span>
    </header>

    <main class="grid gap-5 p-4 lg:grid-cols-[minmax(0,1fr)_24rem] sm:p-6">
        <section class="space-y-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::admin.personal_information')</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach(['email', 'phone', 'date_of_birth', 'gender', 'address'] as $field)
                        <div class="rounded-xl bg-slate-50 p-3">
                            <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::application.'.$field)</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800">
                                @if($field === 'date_of_birth')
                                    {{ $application->date_of_birth?->format('d/m/Y') ?? '—' }}
                                @elseif($field === 'gender' && $application->gender)
                                    @lang('teacher-onboarding::application.genders.'.$application->gender)
                                @else
                                    {{ $application->{$field} ?: '—' }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::admin.teaching_profile')</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::application.subject')</dt><dd class="mt-1 text-sm font-bold text-slate-800">{{ $application->subject?->name ?? '—' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::application.category')</dt><dd class="mt-1 text-sm font-bold text-slate-800">{{ $application->category?->name ?? '—' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::application.education_level')</dt><dd class="mt-1 text-sm font-bold text-slate-800">{{ $application->education_level ? __('teacher-onboarding::application.levels.'.$application->education_level) : '—' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::application.teaching_mode')</dt><dd class="mt-1 text-sm font-bold text-slate-800">@lang('teacher-onboarding::application.modes.'.$application->teaching_mode)</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3 sm:col-span-2"><dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::application.specialization')</dt><dd class="mt-1 text-sm font-bold text-slate-800">{{ $application->specialization }}</dd></div>
                </dl>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::admin.experience_documents')</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="space-y-3">
                        @foreach(['experience_years', 'current_organization', 'previous_organizations', 'achievements', 'experience_description', 'teaching_method', 'intro_video_url'] as $field)
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-onboarding::application.'.$field)</p>
                                <p class="mt-1 whitespace-pre-line text-sm font-semibold leading-6 text-slate-700">{{ $application->{$field} ?: '—' }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="rounded-xl border border-dashed border-slate-200 p-3">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500">@lang('teacher-onboarding::application.documents')</p>
                        <div class="mt-3 space-y-2">
                            @forelse(($application->verification_documents ?? []) as $type => $document)
                            <a href="{{ URL::temporarySignedRoute('admin.teacher-applications.documents.show', now()->addMinutes(15), [$application, $type]) }}" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 no-underline hover:border-green-200 hover:text-green-700">
                                    <span class="min-w-0 truncate">{{ __('teacher-onboarding::application.'.$type.'_document') }}</span>
                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 shrink-0" />
                                </a>
                            @empty
                                <p class="rounded-xl bg-slate-50 p-4 text-center text-sm font-semibold text-slate-400">@lang('teacher-onboarding::admin.no_documents')</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <aside class="space-y-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::interview.schedule_title')</h2>
                <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-onboarding::interview.schedule_desc')</p>
                @if($application->latestInterview)
                    <div class="mt-4 rounded-xl bg-slate-50 p-4">
                        <p class="text-sm font-black text-slate-900">{{ $application->latestInterview->scheduled_at?->format('d/m/Y H:i') }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">@lang('teacher-onboarding::interview.modes.'.$application->latestInterview->mode) · {{ $application->latestInterview->interviewer?->name }}</p>
                    </div>
                    <a href="{{ route('admin.teacher-applications.interviews.show', [$application, $application->latestInterview]) }}" class="mt-4 inline-flex h-10 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 text-sm font-black text-slate-700 no-underline hover:border-green-200 hover:text-green-700"><x-heroicon-o-video-camera class="h-4 w-4" />@lang('teacher-onboarding::interview.view_interview')</a>
                @elseif($application->status === \Mindigo\TeacherOnboarding\Models\TeacherApplication::STATUS_SCREENING)
                    <form method="POST" action="{{ route('admin.teacher-applications.interviews.store', $application) }}" class="mt-4 space-y-4">
                        @csrf
                        <label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.scheduled_at')</span><input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold"></label>
                        <label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.mode')</span><select name="mode" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold"><option value="online">@lang('teacher-onboarding::interview.modes.online')</option><option value="offline">@lang('teacher-onboarding::interview.modes.offline')</option></select></label>
                        <label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.meeting_url')</span><input type="url" name="meeting_url" value="{{ old('meeting_url') }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold" placeholder="https://meet.google.com/..."></label>
                        <label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::interview.pre_interview_note')</span><textarea name="pre_interview_note" rows="4" class="w-full rounded-2xl border border-slate-200 p-4 text-sm font-semibold">{{ old('pre_interview_note') }}</textarea></label>
                        <button class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-green-600 px-5 text-sm font-black text-white"><x-heroicon-o-calendar-days class="h-4 w-4" />@lang('teacher-onboarding::interview.schedule')</button>
                    </form>
                @else
                    <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">@lang('teacher-onboarding::interview.no_interview')</p>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::provisioning.title')</h2>
                        <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-onboarding::provisioning.description')</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700">
                        @lang('teacher-onboarding::provisioning.statuses.'.($application->teacher_provision_status ?? \Mindigo\TeacherOnboarding\Models\TeacherApplication::PROVISION_NOT_PROVISIONED))
                    </span>
                </div>

                <dl class="mt-4 space-y-3">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::provisioning.provisioned_by')</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $application->provisioner?->name ?? __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::provisioning.provisioned_at')</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $application->provisioned_at?->format('d/m/Y H:i') ?? __('teacher-onboarding::interview.empty_value') }}</dd>
                    </div>
                    @if($application->provisioning_note)
                        <div class="rounded-xl bg-slate-50 p-3">
                            <dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::provisioning.note')</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm font-semibold leading-6 text-slate-700">{{ $application->provisioning_note }}</dd>
                        </div>
                    @endif
                </dl>

                <form method="POST" action="{{ route('admin.teacher-applications.provisioning.update', $application) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')
                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::provisioning.action')</span>
                        <select name="action" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold">
                            <option value="approve">@lang('teacher-onboarding::provisioning.actions.approve')</option>
                            <option value="suspend">@lang('teacher-onboarding::provisioning.actions.suspend')</option>
                            <option value="revoke">@lang('teacher-onboarding::provisioning.actions.revoke')</option>
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::provisioning.note')</span>
                        <textarea name="note" rows="4" class="w-full rounded-2xl border border-slate-200 p-4 text-sm font-semibold" placeholder="@lang('teacher-onboarding::provisioning.note_placeholder')">{{ old('note') }}</textarea>
                    </label>
                    <button class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-green-600 px-5 text-sm font-black text-white">
                        <x-heroicon-o-academic-cap class="h-4 w-4" />
                        @lang('teacher-onboarding::provisioning.save')
                    </button>
                </form>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::admin.review_action')</h2>
                @if($nextStatuses)
                    <form method="POST" action="{{ route('admin.teacher-applications.update', $application) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::admin.next_status')</span><select name="status" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold">@foreach($nextStatuses as $status)<option value="{{ $status }}">@lang('teacher-onboarding::admin.statuses.'.$status)</option>@endforeach</select></label>
                        <label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::admin.status_note')</span><textarea name="status_note" rows="4" class="w-full rounded-2xl border border-slate-200 p-4 text-sm font-semibold" placeholder="@lang('teacher-onboarding::admin.status_note_placeholder')">{{ old('status_note') }}</textarea></label>
                        <label class="block space-y-2"><span class="text-xs font-black uppercase text-slate-500">@lang('teacher-onboarding::admin.internal_note')</span><textarea name="internal_note" rows="5" class="w-full rounded-2xl border border-slate-200 p-4 text-sm font-semibold" placeholder="@lang('teacher-onboarding::admin.internal_note_placeholder')">{{ old('internal_note', $application->internal_note) }}</textarea></label>
                        <button class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-green-600 px-5 text-sm font-black text-white"><x-heroicon-o-check class="h-4 w-4" />@lang('teacher-onboarding::admin.save_decision')</button>
                    </form>
                @else
                    <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">@lang('teacher-onboarding::admin.no_next_status')</p>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::admin.review_history')</h2>
                <dl class="mt-4 space-y-3">
                    <div><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::admin.reviewed_by')</dt><dd class="text-sm font-bold text-slate-800">{{ $application->reviewer?->name ?? '—' }}</dd></div>
                    <div><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::admin.reviewed_at')</dt><dd class="text-sm font-bold text-slate-800">{{ $application->reviewed_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::admin.internal_note')</dt><dd class="whitespace-pre-line text-sm font-semibold text-slate-600">{{ $application->internal_note ?: '—' }}</dd></div>
                    <div><dt class="text-[10px] font-black uppercase text-slate-400">@lang('teacher-onboarding::admin.status_note')</dt><dd class="whitespace-pre-line text-sm font-semibold text-slate-600">{{ $application->status_note ?: '—' }}</dd></div>
                </dl>
            </article>
        </aside>
    </main>
</div>
@endsection
