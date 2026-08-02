<article class="flex min-h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white transition hover:border-green-200 hover:shadow-sm">
    <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
        @auth
            @if(auth()->user()->isStudent())
                @php $isWishlisted = in_array($course->id, $wishlistedIds ?? [], true); @endphp
                <form method="POST" action="{{ $isWishlisted ? route('courses.wishlist.destroy', $course) : route('courses.wishlist.store', $course) }}" class="absolute right-3 top-3 z-10">
                    @csrf @if($isWishlisted) @method('DELETE') @endif
                    <button type="submit" aria-label="{{ __($isWishlisted ? 'teacher-course::discovery.remove_wishlist' : 'teacher-course::discovery.add_wishlist') }}" class="grid h-9 w-9 place-items-center rounded-full border border-white/80 bg-white/95 text-green-700 shadow-sm">@if($isWishlisted)<x-heroicon-s-heart class="h-4 w-4" />@else<x-heroicon-o-heart class="h-4 w-4" />@endif</button>
                </form>
            @endif
        @endauth
        @if($course->cover_image)
            <img src="{{ asset('storage/'.$course->cover_image) }}" alt="{{ $course->name }}" class="h-full w-full object-cover">
        @else
            <div class="grid h-full place-items-center text-slate-300"><x-heroicon-o-academic-cap class="h-14 w-14" /></div>
        @endif
        <span class="absolute left-3 top-3 rounded-lg bg-white/95 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-green-700">@lang('teacher-course::catalog.access.'.$course->access_type)</span>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap gap-1.5 text-[10px] font-black uppercase tracking-wide text-slate-500">
            @if($course->subject)<span>{{ $course->subject->name }}</span>@endif
            @if($course->subject && $course->education_level)<span class="text-slate-300">·</span>@endif
            @if($course->education_level)<span>@lang('teacher-course::app.education_levels.'.$course->education_level)</span>@endif
        </div>
        <h2 class="mt-2 line-clamp-2 text-lg font-black leading-6 text-slate-950">{{ $course->name }}</h2>
        <p class="mt-2 flex items-center gap-2 text-xs font-bold text-slate-500">
            <span class="grid h-7 w-7 place-items-center rounded-full bg-green-50 text-[10px] font-black text-green-700">{{ str($course->teacher->name)->substr(0, 1)->upper() }}</span>
            <span class="truncate">{{ $course->teacher->name }}</span>
        </p>
        <div class="mt-4 grid grid-cols-3 gap-2 border-y border-slate-100 py-3 text-center">
            <span><strong class="block text-xs font-black text-slate-800">{{ $course->durationLabel() }}</strong><small class="text-[10px] font-bold text-slate-400">@lang('teacher-course::catalog.duration')</small></span>
            <span><strong class="block text-xs font-black text-slate-800">{{ $course->lessons_count }}</strong><small class="text-[10px] font-bold text-slate-400">@lang('teacher-course::catalog.lessons')</small></span>
            <span><strong class="block text-xs font-black text-amber-600">{{ $course->rating_count ? number_format($course->rating_average, 1) : '—' }}</strong><small class="text-[10px] font-bold text-slate-400">@lang('teacher-course::catalog.rating')</small></span>
        </div>
        <div class="mt-auto flex items-center justify-between gap-3 pt-4">
            <strong class="text-sm font-black text-slate-900">{{ $course->access_type === 'free' ? __('teacher-course::catalog.free') : number_format((float) $course->price).' '.$course->currency }}</strong>
            <a href="{{ route('courses.show', $course->slug) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-green-600 px-3.5 text-xs font-black text-white no-underline transition hover:bg-green-500">
                @lang('teacher-course::catalog.view_detail')<x-heroicon-o-arrow-right class="h-3.5 w-3.5" />
            </a>
        </div>
    </div>
</article>
