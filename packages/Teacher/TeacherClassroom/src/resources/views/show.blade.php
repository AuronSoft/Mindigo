@extends('Mindigo-dashboard::layouts')

@section('title', $classroom->name)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherClassroom/src/resources/css/app.css',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.classrooms.index') }}"
               class="grid h-9 w-9 place-items-center rounded-full border border-slate-200 bg-white text-slate-600 no-underline transition hover:bg-green-50 hover:text-green-700">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
            </a>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">{{ $classroom->code }} · {{ $classroom->school_year ?: '—' }}</p>
                <h1 class="mt-0.5 text-lg font-black text-slate-950">{{ $classroom->name }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.classrooms.edit', $classroom) }}"
               class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-xs font-black text-slate-700 no-underline transition hover:bg-slate-50">
                <x-heroicon-o-pencil-square class="h-4 w-4" />@lang('teacher-classroom::app.edit')
            </a>
            <form method="POST" action="{{ route('teacher.classrooms.destroy', $classroom) }}"
                  data-mindigo-confirm-title="@lang('teacher-classroom::app.delete_title')"
                  data-mindigo-confirm-message="@lang('teacher-classroom::app.delete_confirm')"
                  data-mindigo-confirm-text="@lang('teacher-classroom::app.delete')"
                  data-mindigo-confirm-cancel="{{ __('teacher-classroom::app.cancel') }}"
                  data-mindigo-confirm-type="danger">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex h-9 items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 text-xs font-black text-red-600 transition hover:bg-red-100">
                    <x-heroicon-o-trash class="h-4 w-4" />@lang('teacher-classroom::app.delete')
                </button>
            </form>
        </div>
    </header>

    <div class="flex flex-1 flex-col gap-5 p-6">

        {{-- Mini stats --}}
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach([
                ['label' => 'Trạng thái',  'value' => __('teacher-classroom::app.' . $classroom->status), 'tone' => $classroom->status === 'active' ? 'text-green-700 bg-green-50' : 'text-slate-600 bg-slate-100'],
                ['label' => __('teacher-classroom::app.stat_label_students'), 'value' => __('teacher-classroom::app.students_count_badge', ['count' => $classroom->students->count()]), 'tone' => 'text-sky-700 bg-sky-50'],
                ['label' => __('teacher-classroom::app.stat_label_subjects'),  'value' => __('teacher-classroom::app.subjects_count_badge', ['count' => $classroom->subjects->count()]),  'tone' => 'text-violet-700 bg-violet-50'],
            ] as $stat)
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                        <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-black {{ $stat['tone'] }}">{{ $stat['value'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,0.38fr)_minmax(0,0.62fr)]">

            {{-- Left: info + subjects --}}
            <div class="space-y-4">
                {{-- Info card --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-classroom::app.class_info')</p>
                    <div class="space-y-2">
                        @foreach([
                            [__('teacher-classroom::app.info_class_code'),  $classroom->code],
                            [__('teacher-classroom::app.info_school_year'), $classroom->school_year ?: '—'],
                        ] as $row)
                            <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5 text-sm">
                                <span class="font-bold text-slate-500">{{ $row[0] }}</span>
                                <span class="font-black text-slate-900">{{ $row[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if($classroom->description)
                        <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2.5 text-sm font-semibold leading-relaxed text-slate-500">{{ $classroom->description }}</p>
                    @endif
                </div>

                {{-- Subjects --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">@lang('teacher-classroom::app.subject_list')</p>
                    @if($classroom->subjects->isEmpty())
                        <div class="flex flex-col items-center gap-2 py-6">
                            <x-heroicon-o-book-open class="h-9 w-9 text-slate-200" />
                            <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.no_subjects')</p>
                        </div>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($classroom->subjects as $subject)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-black text-slate-700">
                                    <span class="h-2 w-2 rounded-full" style="background:{{ $subject->color ?: '#94a3b8' }}"></span>
                                    {{ $subject->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: students --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <p class="text-sm font-black text-slate-950">@lang('teacher-classroom::app.student_list')</p>
                        <p class="mt-0.5 text-xs font-bold text-slate-400">@lang('teacher-classroom::app.students_in_class', ['count' => $classroom->students->count()])</p>
                    </div>
                </div>

                @if($classroom->students->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-20">
                        <x-heroicon-o-academic-cap class="h-14 w-14 text-slate-200" />
                        <p class="text-base font-black text-slate-600">@lang('teacher-classroom::app.no_students')</p>
                        <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.add_students_hint')</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="w-10 px-5 py-3">@lang('teacher-classroom::app.col_number')</th>
                                    <th class="px-5 py-3">@lang('teacher-classroom::app.col_student')</th>
                                    <th class="px-5 py-3">@lang('teacher-classroom::app.col_email')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($classroom->students as $i => $student)
                                    <tr class="bg-white transition hover:bg-slate-50/80">
                                        <td class="px-5 py-3 text-sm font-black text-slate-400">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-linear-to-br from-sky-200 to-sky-400 text-xs font-black text-white">
                                                    {{ mb_substr($student->name, 0, 1) }}
                                                </span>
                                                <span class="text-sm font-black text-slate-900">{{ $student->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-sm font-bold text-slate-400">{{ $student->email }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
