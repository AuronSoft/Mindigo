@props(['exam', 'status'])

<a href="{{ $status === 'ongoing' ? route('student.exams.start', $exam) : '#' }}"
   class="group block overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md {{ $status === 'completed' ? 'opacity-75' : '' }}">

    <div class="h-2 bg-gradient-to-r {{ $status === 'ongoing' ? 'from-green-500 to-emerald-500' : ($status === 'upcoming' ? 'from-amber-500 to-yellow-500' : 'from-slate-400 to-slate-500') }}"></div>

    <div class="p-5">
        <div class="flex justify-between items-start">
            <h3 class="font-black text-slate-800 leading-tight line-clamp-2">{{ $exam->title }}</h3>
            @if($status === 'ongoing')
                <span class="px-3 py-1 text-xs font-black rounded-full bg-green-100 text-green-700">Đang mở</span>
            @endif
        </div>

        <p class="text-sm text-slate-500 mt-1">{{ $exam->classroom?->name ?? '' }}</p>

        <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
            <div>
                <p class="text-slate-400">@lang('student-exam::app.duration')</p>
                <p class="font-bold text-slate-700">{{ $exam->duration_minutes ? $exam->duration_minutes . ' phút' : __('student-exam::app.no_limit') }}</p>
            </div>
            <div>
                <p class="text-slate-400">@lang('student-exam::app.max_attempts')</p>
                <p class="font-bold text-slate-700">{{ $exam->max_attempts ?? 1 }} @lang('student-exam::app.times')</p>
            </div>
        </div>

        @if($status === 'completed' && $exam->attempts->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-slate-100 text-xs">
                <p class="text-slate-400">Kết quả gần nhất</p>
                <p class="font-black text-green-600">
                    {{ $exam->attempts->first()->percentage ?? 0 }}% 
                    @if($exam->attempts->first()->passed) • Đậu @else • Chưa đạt @endif
                </p>
            </div>
        @endif
    </div>
</a>