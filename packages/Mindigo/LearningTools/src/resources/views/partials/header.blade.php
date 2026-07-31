@if(request()->routeIs('learning-tools.gpa.*', 'learning-tools.academic.*'))
    @php
        $actionRoute = route('learning-tools.scores.index');
        $actionLabel = __('learning-tools::app.scores.back_to_calculator');
    @endphp
@endif
<header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
    <div>
        <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ $eyebrow }}</p>
        <h1 class="mt-0.5 text-lg font-black text-slate-950">{{ $title }}</h1>
        <p class="text-xs font-semibold text-slate-400">{{ $subtitle }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('learning-tools.index') }}" class="inline-flex h-10 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 no-underline transition hover:border-green-200 hover:text-green-700">
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            @lang('learning-tools::app.back_to_tools')
        </a>
        @if(request()->routeIs('learning-tools.scores.*'))
            <a href="{{ route('learning-tools.gpa.index') }}" class="inline-flex h-10 items-center rounded-full border border-green-200 bg-green-50 px-5 text-sm font-black text-green-700 no-underline transition hover:bg-green-100">
                @lang('learning-tools::app.gpa.open')
            </a>
        @endif
        @if(isset($actionRoute, $actionLabel))
            <a href="{{ $actionRoute }}" class="inline-flex h-10 items-center rounded-full bg-green-600 px-5 text-sm font-black text-white no-underline shadow-sm shadow-green-200 transition hover:bg-green-500">
                {{ $actionLabel }}
            </a>
        @endif
    </div>
</header>
