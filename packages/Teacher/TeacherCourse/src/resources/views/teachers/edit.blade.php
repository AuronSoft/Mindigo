@extends('Mindigo-dashboard::layouts')
@section('title', __('teacher-course::reviews.edit_profile'))
@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection
@section('content')
<main class="min-h-screen bg-slate-50"><header class="border-b border-slate-200 bg-white px-6 py-4"><div class="flex items-center gap-4"><a href="{{ route('teacher.courses.index') }}" aria-label="@lang('teacher-course::reviews.back_to_courses')" title="@lang('teacher-course::reviews.back_to_courses')" class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700"><x-heroicon-o-arrow-left class="h-4 w-4" /></a><div class="min-w-0"><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('teacher-course::reviews.profile_title')</p><h1 class="text-lg font-black text-slate-950">@lang('teacher-course::reviews.edit_profile')</h1><p class="text-xs font-semibold text-slate-400">@lang('teacher-course::reviews.profile_subtitle')</p></div></div></header><div class="mx-auto max-w-3xl p-6"><form method="POST" action="{{ route('teacher.profile.update', $profile) }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">@csrf @method('PUT')
    @foreach([['headline','text'],['specialization','text'],['experience_years','number']] as [$name,$type])<label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::reviews.'.$name)</span><input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $profile->{$name}) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label>@endforeach
    <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::reviews.biography')</span><textarea name="biography" rows="5" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ old('biography', $profile->biography) }}</textarea></label>
    <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::reviews.qualifications')</span><textarea name="qualifications" rows="4" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ old('qualifications', implode("\n", $profile->qualifications ?? [])) }}</textarea></label>
    <div class="grid gap-3 sm:grid-cols-3">
        @foreach(['website', 'facebook', 'linkedin'] as $network)
            <label class="block"><span class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-course::reviews.social_'.$network)</span><input type="url" name="social_links[{{ $network }}]" value="{{ old('social_links.'.$network, data_get($profile->social_links, $network)) }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></label>
        @endforeach
    </div>
    <label class="flex items-center gap-2 text-sm font-black text-slate-700"><input type="hidden" name="is_public" value="0"><input type="checkbox" name="is_public" value="1" @checked(old('is_public', $profile->is_public)) class="h-4 w-4 accent-green-600">@lang('teacher-course::reviews.public')</label>
    <div class="flex justify-end"><button class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-black text-white">@lang('teacher-course::reviews.save_profile')</button></div>
</form></div></main>
@endsection
