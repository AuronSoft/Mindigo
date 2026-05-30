@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-report::app.student_reports'))

@section('styles')
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
                    <h1 class="mt-0.5 text-xl font-black text-slate-950">@lang('Mindigo-report::app.student_reports')</h1>
                </div>
            </div>
        </header>

        <div class="px-5 pb-8 max-md:px-4 space-y-4">

            {{-- Top 5 students highlight --}}
            @if(!empty($topStudents) && $topStudents->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach($topStudents as $i => $s)
                        <a href="{{ route('reports.student.detail', $s->id) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm no-underline transition hover:border-green-200 hover:shadow-md block">
                            <div class="flex items-center gap-2">
                                <span class="grid h-9 w-9 place-items-center rounded-full {{ $i === 0 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }} text-sm font-black">{{ mb_substr($s->name, 0, 1) }}</span>
                                <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-100 text-[10px] font-black text-slate-500">{{ $i + 1 }}</span>
                            </div>
                            <p class="mt-3 text-sm font-black text-slate-900 leading-snug line-clamp-1">{{ $s->name }}</p>
                            <p class="mt-1 text-xs font-bold text-slate-400">{{ $s->attempt_count }} lượt · TB {{ $s->avg_score }}%</p>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Full students table --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="text-sm font-black text-slate-950">Tất cả học viên</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase text-slate-400">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.name')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.email')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.attempts')</th>
                                <th class="px-4 py-3">@lang('Mindigo-report::app.avg_percent')</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-bold text-slate-700">
                            @forelse($students as $i => $student)
                                <tr class="bg-white hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-slate-400">{{ ($students->currentPage() - 1) * $students->perPage() + $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-green-100 text-xs font-black text-green-700">{{ mb_substr($student->name, 0, 1) }}</span>
                                            <span class="font-black text-slate-900">{{ $student->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $student->email }}</td>
                                    <td class="px-4 py-3">{{ number_format($student->attempts_count ?? 0) }}</td>
                                    <td class="px-4 py-3">
                                        @if(($student->attempts_count ?? 0) > 0)
                                            <span class="rounded-full bg-green-50 px-2.5 py-1 text-[11px] font-black text-green-700">{{ $student->attempts_avg_percentage ?? '—' }}%</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('reports.student.detail', $student) }}" class="text-xs font-black text-slate-400 no-underline hover:text-green-600">@lang('Mindigo-report::app.view_detail')</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center font-bold text-slate-400">@lang('Mindigo-report::app.no_data')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($students->hasPages())
                    <div class="border-t border-slate-100 px-5 py-3">{{ $students->links() }}</div>
                @endif
            </div>

        </div>
    </section>
@endsection
