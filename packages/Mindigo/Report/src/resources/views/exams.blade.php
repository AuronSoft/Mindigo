@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-report::app.exam_reports'))

@section('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite([
        'packages/Mindigo/Report/src/resources/css/app.css',
        'packages/Mindigo/Report/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <section class="min-h-screen bg-[#f7faf7]">
        <header class="flex min-h-[4.25rem] items-center justify-between gap-4 bg-[#f7faf7] px-5 py-3 max-md:px-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.index') }}" class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                </a>
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">@lang('Mindigo-report::app.title')</p>
                    <h1 class="mt-0.5 text-xl font-black text-slate-950">@lang('Mindigo-report::app.exam_reports')</h1>
                </div>
            </div>
        </header>

        <div class="px-5 pb-8 max-md:px-4 space-y-4">

            {{-- Top 5 exams highlight --}}
            @if($topExams->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach($topExams as $i => $exam)
                        <a href="{{ route('reports.exam.detail', $exam) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm no-underline transition hover:border-green-200 hover:shadow-md block">
                            <div class="flex items-center justify-between gap-2">
                                <span class="grid h-7 w-7 place-items-center rounded-full {{ $i === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} text-xs font-black">{{ $i + 1 }}</span>
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-black text-green-700">{{ number_format($exam->attempts_count) }}</span>
                            </div>
                            <p class="mt-3 text-sm font-black text-slate-900 leading-snug line-clamp-2">{{ $exam->title }}</p>
                            <p class="mt-2 text-xs font-bold text-slate-400">TB: {{ round($exam->attempts_avg_percentage ?? 0, 1) }}%</p>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Full exams table --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="text-sm font-black text-slate-950">Tất cả đề thi</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.exam_name')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.subject')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.attempts')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.avg_percent')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.pass_rate')</th>
                                <th class="px-4 py-3">Trạng thái</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                            @forelse($exams as $i => $exam)
                                @php
                                    $statusTone = match($exam->status) {
                                        'published' => 'bg-green-100 text-green-800',
                                        'reviewing' => 'bg-amber-100 text-amber-800',
                                        'closed' => 'bg-red-100 text-red-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <tr class="bg-white hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-slate-400">{{ ($exams->currentPage() - 1) * $exams->perPage() + $i + 1 }}</td>
                                    <td class="px-4 py-3 max-w-xs truncate">{{ \Illuminate\Support\Str::limit($exam->title, 40) }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $exam->subject ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ number_format($exam->attempts_count) }}</td>
                                    <td class="px-4 py-3">{{ $exam->attempts_count > 0 ? round($exam->attempts_avg_percentage ?? 0, 1) . '%' : '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($exam->attempts_count > 0)
                                            @php
                                                $passed = \Mindigo\ExamManagement\Models\ExamAttempt::where('exam_id', $exam->id)->where('status', 'submitted')->where('passed', true)->count();
                                                $pr = $exam->attempts_count > 0 ? round($passed / $exam->attempts_count * 100) : 0;
                                            @endphp
                                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-black text-emerald-700">{{ $pr }}%</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusTone }}">{{ $exam->status }}</span></td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('reports.exam.detail', $exam) }}" class="text-xs font-black text-slate-400 no-underline hover:text-green-600">@lang('Mindigo-report::app.view_detail')</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-8 text-center font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($exams->hasPages())
                    <div class="border-t border-slate-100 px-5 py-3">{{ $exams->links() }}</div>
                @endif
            </div>

        </div>
    </section>
@endsection
