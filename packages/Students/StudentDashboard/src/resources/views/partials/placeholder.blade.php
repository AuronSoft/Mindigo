{{-- Shared placeholder cho các module Student đang dựng khung --}}
{{-- Biến: $title (tên module) --}}
<div class="flex flex-col gap-6 p-6 max-md:p-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-black tracking-tight text-slate-900 max-md:text-xl">{{ $title ?? '' }}</h1>
        @if(Route::has('student.dashboard'))
            <a href="{{ route('student.dashboard') }}" class="rounded-xl bg-white px-4 py-2 text-xs font-extrabold text-slate-600 shadow-sm ring-1 ring-slate-100 no-underline transition hover:text-green-700">
                ← {{ __('student-dashboard::app.nav_dashboard') }}
            </a>
        @endif
    </div>

    <section class="grid place-items-center rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-20 text-center shadow-sm">
        <span class="grid h-16 w-16 place-items-center rounded-2xl bg-green-50 text-green-600">
            <svg viewBox="0 0 24 24" class="h-8 w-8 fill-none stroke-current stroke-2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
        </span>
        <h2 class="mt-5 text-lg font-black text-slate-800">{{ __('student-dashboard::app.coming_soon') }}</h2>
        <p class="mt-2 max-w-md text-sm font-semibold text-slate-400">{{ __('student-dashboard::app.coming_soon_desc') }}</p>
    </section>
</div>
