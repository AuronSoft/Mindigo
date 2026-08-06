@php
    $scheduleDays = collect($course->schedule_days ?? [])
        ->map(fn (string $day) => __('teacher-course::app.schedule_days.'.$day))
        ->filter()
        ->implode(', ');
@endphp

<article class="flex min-h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-green-200 hover:shadow-sm">
    <div class="relative aspect-video overflow-hidden bg-slate-950">
        @auth
            @if(auth()->user()->isStudent())
                @php $isWishlisted = in_array($course->id, $wishlistedIds ?? [], true); @endphp
                <form method="POST" action="{{ $isWishlisted ? route('courses.wishlist.destroy', $course) : route('courses.wishlist.store', $course) }}" class="absolute right-3 top-3 z-10">
                    @csrf
                    @if($isWishlisted) @method('DELETE') @endif
                    <button type="submit" aria-label="{{ __($isWishlisted ? 'teacher-course::discovery.remove_wishlist' : 'teacher-course::discovery.add_wishlist') }}" class="grid h-9 w-9 place-items-center rounded-full border border-white/80 bg-white/95 text-green-700 shadow-sm">
                        @if($isWishlisted)<x-heroicon-s-heart class="h-4 w-4" />@else<x-heroicon-o-heart class="h-4 w-4" />@endif
                    </button>
                </form>
            @endif
        @endauth

        @if($course->cover_image)
            <img src="{{ asset('storage/'.$course->cover_image) }}" alt="{{ $course->name }}" class="h-full w-full object-contain">
        @else
            <div class="grid h-full place-items-center text-slate-300"><x-heroicon-o-academic-cap class="h-14 w-14" /></div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap gap-2 text-[10px] font-black">
            <span class="rounded-full bg-green-50 px-2.5 py-1 uppercase tracking-wide text-green-700">@lang('teacher-course::catalog.access.'.$course->access_type)</span>
            @if($course->subject)<span class="rounded-full bg-slate-50 px-2.5 py-1 text-slate-600">{{ $course->subject->name }}</span>@endif
            @if($course->category)<span class="rounded-full bg-slate-50 px-2.5 py-1 text-slate-600">{{ $course->category->name }}</span>@endif
        </div>

        <h2 class="mt-2 line-clamp-2 text-lg font-black leading-6 text-slate-950">{{ $course->name }}</h2>
        <p class="mt-2 line-clamp-2 text-xs font-semibold leading-5 text-slate-500">{{ $course->description ?: __('teacher-course::catalog.no_description') }}</p>

        <p class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-500">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-green-50 text-[10px] font-black text-green-700">{{ str($course->teacher->name)->substr(0, 1)->upper() }}</span>
            <span class="truncate">{{ $course->teacher->name }}</span>
        </p>

        <div class="mt-4 rounded-xl bg-green-50 px-3 py-2 text-center text-base font-black text-green-700">
            {{ $course->access_type === 'free' ? __('teacher-course::catalog.free') : number_format((float) $course->price).' '.$course->currency }}
        </div>

        <dl class="mt-3 divide-y divide-slate-100 text-xs">
            <div class="flex justify-between gap-3 py-2">
                <dt class="font-bold text-slate-400">@lang('teacher-course::catalog.starts_at')</dt>
                <dd class="font-black text-slate-700">{{ $course->starts_at?->format('d/m/Y') ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-3 py-2">
                <dt class="font-bold text-slate-400">@lang('teacher-course::catalog.schedule_days')</dt>
                <dd class="max-w-40 text-right font-black text-slate-700">{{ $scheduleDays ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-3 py-2">
                <dt class="font-bold text-slate-400">@lang('teacher-course::catalog.study_time')</dt>
                <dd class="font-black text-slate-700">{{ $course->study_time ?: '—' }}</dd>
            </div>
        </dl>

        <div class="mt-3 grid grid-cols-3 gap-2 border-y border-slate-100 py-3 text-center">
            <span><strong class="block text-xs font-black text-slate-800">{{ $course->durationLabel() }}</strong><small class="text-[10px] font-bold text-slate-400">@lang('teacher-course::catalog.duration')</small></span>
            <span><strong class="block text-xs font-black text-slate-800">{{ $course->lessons_count }}</strong><small class="text-[10px] font-bold text-slate-400">@lang('teacher-course::catalog.lessons')</small></span>
            <span><strong class="block text-xs font-black text-green-700">{{ number_format((int) $course->enrollment_count) }}</strong><small class="text-[10px] font-bold text-slate-400">@lang('teacher-course::catalog.students')</small></span>
        </div>

        <div class="mt-auto pt-4">
            <a href="{{ route('courses.show', $course->slug) }}" class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-green-600 px-3.5 text-xs font-black text-white no-underline transition hover:bg-green-500">
                @lang('teacher-course::catalog.view_detail')<x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
            </a>
        </div>
    </div>
</article>
