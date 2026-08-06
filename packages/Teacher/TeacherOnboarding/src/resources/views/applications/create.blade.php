@extends('core::layouts.home')

@section('title', __('teacher-onboarding::application.title').' - Mindigo')
@section('meta_description', __('teacher-onboarding::application.subtitle'))
@section('canonical', route('teacher-applications.create'))

@section('content')
<div class="bg-slate-50 text-slate-900">
    @include('core::partials.home.navbar')

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-8 sm:px-8 lg:grid-cols-[minmax(0,1fr)_24rem] lg:px-10">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-green-700">@lang('teacher-onboarding::application.eyebrow')</p>
                <h1 class="mt-2 max-w-3xl text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">@lang('teacher-onboarding::application.title')</h1>
                <p class="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-500">@lang('teacher-onboarding::application.subtitle')</p>
                <a href="#teacher-application-form" class="mt-5 inline-flex h-11 items-center justify-center rounded-2xl bg-green-600 px-5 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                    @lang('teacher-onboarding::application.cta')
                </a>
            </div>
            <aside class="rounded-3xl border border-green-100 bg-green-50 p-5">
                <h2 class="text-base font-black text-green-900">@lang('teacher-onboarding::application.intro_title')</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-green-800">@lang('teacher-onboarding::application.intro_desc')</p>
                <p class="mt-4 rounded-2xl bg-white px-4 py-3 text-xs font-black text-green-700">@lang('teacher-onboarding::application.review_time')</p>
            </aside>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-6 sm:px-8 lg:px-10">
        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-green-100 bg-green-50 px-5 py-4 text-sm font-bold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[20rem_minmax(0,1fr)]">
            <aside class="space-y-4">
                <section class="rounded-3xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-onboarding::application.process')</h2>
                    <ol class="mt-4 space-y-3">
                        @foreach(['process_submit', 'process_screening', 'process_interview'] as $index => $step)
                            <li class="flex gap-3 text-sm font-semibold text-slate-600">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-green-50 text-xs font-black text-green-700">{{ $index + 1 }}</span>
                                <span>{{ __('teacher-onboarding::application.'.$step) }}</span>
                            </li>
                        @endforeach
                    </ol>
                </section>
                <section class="rounded-3xl border border-slate-200 bg-white p-5">
                    <h2 class="text-sm font-black text-slate-950">@lang('teacher-onboarding::application.benefits')</h2>
                    <div class="mt-4 space-y-3">
                        @foreach(['benefit_profile', 'benefit_courses', 'benefit_tools'] as $benefit)
                            <div class="flex gap-3 text-sm font-semibold text-slate-600">
                                <x-heroicon-o-check-circle class="h-5 w-5 shrink-0 text-green-600" />
                                <span>{{ __('teacher-onboarding::application.'.$benefit) }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>

            <form id="teacher-application-form" method="POST" action="{{ route('teacher-applications.store') }}" enctype="multipart/form-data" class="space-y-5 rounded-3xl border border-slate-200 bg-white p-5 sm:p-6">
                @csrf

                <section>
                    <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::application.personal_info')</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.full_name') *</span>
                            <input name="full_name" value="{{ old('full_name', $user?->name) }}" required class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.email') *</span>
                            <input type="email" name="email" value="{{ old('email', $user?->email) }}" required class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.phone') *</span>
                            <input name="phone" value="{{ old('phone', $user?->phone) }}" required class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.date_of_birth')</span>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user?->date_of_birth?->format('Y-m-d')) }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.gender')</span>
                            <select name="gender" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                                <option value="">@lang('teacher-onboarding::application.select_placeholder')</option>
                                @foreach($genders as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender', $user?->gender) === $gender)>@lang('teacher-onboarding::application.genders.'.$gender)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.address')</span>
                            <input name="address" value="{{ old('address', $user?->address) }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                    </div>
                </section>

                <section class="border-t border-slate-100 pt-5">
                    <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::application.teaching_info')</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.application_type') *</span>
                            <select name="application_type" required class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                                @foreach($applicationTypes as $type)
                                    <option value="{{ $type }}" @selected(old('application_type') === $type)>@lang('teacher-onboarding::application.types.'.$type)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.teaching_mode') *</span>
                            <select name="teaching_mode" required class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                                @foreach($teachingModes as $mode)
                                    <option value="{{ $mode }}" @selected(old('teaching_mode') === $mode)>@lang('teacher-onboarding::application.modes.'.$mode)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.subject')</span>
                            <select name="subject_id" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                                <option value="">@lang('teacher-onboarding::application.select_placeholder')</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.category')</span>
                            <select name="category_id" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                                <option value="">@lang('teacher-onboarding::application.select_placeholder')</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.education_level')</span>
                            <select name="education_level" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                                <option value="">@lang('teacher-onboarding::application.select_placeholder')</option>
                                @foreach($educationLevels as $level)
                                    <option value="{{ $level }}" @selected(old('education_level') === $level)>@lang('teacher-onboarding::application.levels.'.$level)</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.specialization') *</span>
                            <input name="specialization" value="{{ old('specialization') }}" required class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                    </div>
                </section>

                <section class="border-t border-slate-100 pt-5">
                    <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::application.experience')</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.experience_years') *</span>
                            <input type="number" name="experience_years" min="0" max="60" value="{{ old('experience_years', 0) }}" required class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.current_organization')</span>
                            <input name="current_organization" value="{{ old('current_organization') }}" class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        @foreach(['previous_organizations', 'achievements', 'experience_description'] as $field)
                            <label class="block">
                                <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.'.$field)</span>
                                <textarea name="{{ $field }}" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:border-green-400">{{ old($field) }}</textarea>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="border-t border-slate-100 pt-5">
                    <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::application.documents')</h2>
                    <p class="mt-1 text-xs font-semibold text-slate-400">@lang('teacher-onboarding::application.file_hint')</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @foreach(['identity_document', 'degree_document', 'certificate_document', 'student_card_document', 'cv_document', 'portfolio_document'] as $field)
                            <label class="block">
                                <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.'.$field)</span>
                                <input type="file" name="{{ $field }}" class="block h-11 w-full rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 file:mr-3 file:rounded-full file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-xs file:font-black file:text-green-700">
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="border-t border-slate-100 pt-5">
                    <h2 class="text-base font-black text-slate-950">@lang('teacher-onboarding::application.method')</h2>
                    <div class="mt-4 grid gap-4">
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.teaching_method') *</span>
                            <textarea name="teaching_method" rows="5" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:border-green-400">{{ old('teaching_method') }}</textarea>
                        </label>
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-onboarding::application.intro_video_url') <span class="text-slate-400">(@lang('teacher-onboarding::application.optional'))</span></span>
                            <input name="intro_video_url" value="{{ old('intro_video_url') }}" placeholder="https://youtube.com/..." class="h-11 w-full rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-400">
                        </label>
                        <label class="flex items-start gap-3 rounded-2xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-bold text-green-800">
                            <input type="checkbox" name="terms_accepted" value="1" required @checked(old('terms_accepted')) class="mt-1 h-4 w-4 rounded border-green-300 accent-green-600">
                            <span>@lang('teacher-onboarding::application.terms_accepted')</span>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-2xl bg-green-600 px-6 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                        @lang('teacher-onboarding::application.submit')
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
