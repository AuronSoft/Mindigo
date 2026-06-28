@extends('Mindigo-dashboard::layouts')

@section('title', $classroom->name)

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Teacher/TeacherClassroom/src/resources/css/app.css',
    ])
    <style>
        .tab-btn.active {
            border-color: #16a34a;
            color: #16a34a;
        }
        .modal {
            transition: opacity 0.25s ease;
        }
    </style>
@endsection

@section('content')
@php
    $currentTab = request('tab', 'students');
@endphp
<div class="flex min-h-screen flex-col bg-slate-50" id="classroom-detail-page">

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

        {{-- Mini stats / Info --}}
        <div class="grid gap-3 sm:grid-cols-4">
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-classroom::app.status')</p>
                    <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-black {{ $classroom->status === 'active' ? 'text-green-700 bg-green-50' : 'text-slate-600 bg-slate-100' }}">
                        {{ __('teacher-classroom::app.' . $classroom->status) }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-classroom::app.students')</p>
                    <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-black text-sky-700 bg-sky-50">
                        {{ __('teacher-classroom::app.students_count', ['count' => $classroom->students->count()]) }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-classroom::app.assistant')</p>
                    <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-black text-indigo-700 bg-indigo-50">
                        {{ $classroom->assistant ? $classroom->assistant->name : __('teacher-classroom::app.unassigned') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">@lang('teacher-classroom::app.subjects')</p>
                    <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-black text-violet-700 bg-violet-50">
                        {{ __('teacher-classroom::app.subjects_count', ['count' => $classroom->subjects->count()]) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="border-b border-slate-200 bg-white rounded-t-3xl px-6 pt-3 shadow-sm">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <button onclick="switchTab('students')" id="tab-btn-students" class="tab-btn pb-4 px-1 border-b-2 border-transparent text-sm font-black text-slate-500 hover:text-slate-800 transition-all {{ $currentTab === 'students' ? 'active' : '' }}">
                    @lang('teacher-classroom::app.students_and_assistants')
                </button>
                <button onclick="switchTab('attendance')" id="tab-btn-attendance" class="tab-btn pb-4 px-1 border-b-2 border-transparent text-sm font-black text-slate-500 hover:text-slate-800 transition-all {{ $currentTab === 'attendance' ? 'active' : '' }}">
                    @lang('teacher-classroom::app.attendance')
                </button>
                <button onclick="switchTab('schedule')" id="tab-btn-schedule" class="tab-btn pb-4 px-1 border-b-2 border-transparent text-sm font-black text-slate-500 hover:text-slate-800 transition-all {{ $currentTab === 'schedule' ? 'active' : '' }}">
                    @lang('teacher-classroom::app.schedule')
                </button>
                <button onclick="switchTab('announcements')" id="tab-btn-announcements" class="tab-btn pb-4 px-1 border-b-2 border-transparent text-sm font-black text-slate-500 hover:text-slate-800 transition-all {{ $currentTab === 'announcements' ? 'active' : '' }}">
                    @lang('teacher-classroom::app.announcements')
                </button>
            </nav>
        </div>

        {{-- Tab Contents --}}
        <div class="bg-white rounded-b-3xl border border-slate-200 border-t-0 p-6 shadow-sm min-h-[400px]">

            {{-- Tab 1: Students & Assistants --}}
            <div id="tab-content-students" class="tab-content {{ $currentTab === 'students' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h2 class="text-base font-black text-slate-900">@lang('teacher-classroom::app.student_list_title')</h2>
                        <p class="text-xs font-bold text-slate-400">@lang('teacher-classroom::app.student_list_subtitle')</p>
                    </div>
                    <button onclick="openModal('students-modal')" class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white hover:bg-green-500 transition shadow-sm shadow-green-100">
                        <x-heroicon-o-user-plus class="h-4 w-4" /> @lang('teacher-classroom::app.manage_students')
                    </button>
                </div>

                @if($classroom->students->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-20">
                        <x-heroicon-o-academic-cap class="h-14 w-14 text-slate-200" />
                        <p class="text-base font-black text-slate-600">@lang('teacher-classroom::app.no_students_in_classroom')</p>
                        <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.add_students_hint_click')</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="w-10 px-5 py-3">@lang('teacher-classroom::app.col_number')</th>
                                    <th class="px-5 py-3">@lang('teacher-classroom::app.col_student')</th>
                                    <th class="px-5 py-3">@lang('teacher-classroom::app.col_email')</th>
                                    <th class="px-5 py-3 text-right">@lang('teacher-classroom::app.actions')</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($classroom->students as $i => $student)
                                    <tr class="bg-white transition hover:bg-slate-50/80">
                                        <td class="px-5 py-3 text-sm font-black text-slate-400">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-gradient-to-br from-green-200 to-green-400 text-xs font-black text-white">
                                                    {{ mb_substr($student->name, 0, 1) }}
                                                </span>
                                                <span class="text-sm font-black text-slate-900">{{ $student->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-sm font-bold text-slate-400">{{ $student->email }}</td>
                                        <td class="px-5 py-3 text-sm text-right">
                                            <form method="POST" action="{{ route('teacher.classrooms.students.sync', $classroom) }}" class="inline"
                                                  data-mindigo-confirm-title="{{ __('teacher-classroom::app.delete_student_title') }}"
                                                  data-mindigo-confirm-message="{{ __('teacher-classroom::app.delete_student_confirm', ['name' => $student->name]) }}"
                                                  data-mindigo-confirm-text="{{ __('teacher-classroom::app.delete_student') }}"
                                                  data-mindigo-confirm-cancel="{{ __('teacher-classroom::app.cancel') }}"
                                                  data-mindigo-confirm-type="danger">
                                                @csrf
                                                @foreach($classroom->students as $s)
                                                    @if($s->id !== $student->id)
                                                        <input type="hidden" name="student_ids[]" value="{{ $s->id }}">
                                                    @endif
                                                @endforeach
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold">@lang('teacher-classroom::app.delete_student')</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Tab 2: Attendance --}}
            <div id="tab-content-attendance" class="tab-content {{ $currentTab === 'attendance' ? '' : 'hidden' }}">
                <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-4 mb-4 gap-3">
                    <div>
                        <h2 class="text-base font-black text-slate-900">@lang('teacher-classroom::app.attendance_title')</h2>
                        <p class="text-xs font-bold text-slate-400">@lang('teacher-classroom::app.attendance_subtitle')</p>
                    </div>
                    <form method="GET" action="{{ route('teacher.classrooms.show', $classroom) }}" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="attendance">
                        <input type="date" name="attendance_date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400">
                    </form>
                </div>

                @if($classroom->students->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-20">
                        <x-heroicon-o-academic-cap class="h-14 w-14 text-slate-200" />
                        <p class="text-base font-black text-slate-600">@lang('teacher-classroom::app.no_students_to_attendance')</p>
                        <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.add_students_first')</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('teacher.classrooms.attendance.save', $classroom) }}">
                        @csrf
                        <input type="hidden" name="attendance_date" value="{{ $selectedDate }}">
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 mb-6">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-400">
                                    <tr>
                                        <th class="px-5 py-3">@lang('teacher-classroom::app.col_student')</th>
                                        <th class="px-5 py-3">@lang('teacher-classroom::app.present_label')</th>
                                        <th class="px-5 py-3">@lang('teacher-classroom::app.absent_label')</th>
                                        <th class="px-5 py-3">@lang('teacher-classroom::app.late_label')</th>
                                        <th class="px-5 py-3">@lang('teacher-classroom::app.excused_label')</th>
                                        <th class="px-5 py-3">@lang('teacher-classroom::app.remarks')</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($classroom->students as $student)
                                        @php
                                            $record = $attendanceRecords->get($student->id);
                                            $status = $record ? $record->status : 'present';
                                            $remarks = $record ? $record->remarks : '';
                                        @endphp
                                        <tr class="bg-white transition hover:bg-slate-50/50">
                                            <td class="px-5 py-3.5">
                                                <span class="text-sm font-black text-slate-900">{{ $student->name }}</span>
                                                <span class="block text-[11px] font-bold text-slate-400">{{ $student->email }}</span>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="records[{{ $student->id }}][status]" value="present" @checked($status === 'present') class="h-4 w-4 accent-green-600">
                                                </label>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="records[{{ $student->id }}][status]" value="absent" @checked($status === 'absent') class="h-4 w-4 accent-red-600">
                                                </label>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="records[{{ $student->id }}][status]" value="late" @checked($status === 'late') class="h-4 w-4 accent-yellow-600">
                                                </label>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="records[{{ $student->id }}][status]" value="excused" @checked($status === 'excused') class="h-4 w-4 accent-blue-600">
                                                </label>
                                            </td>
                                            <td class="px-5 py-3.5">
                                                <input type="text" name="records[{{ $student->id }}][remarks]" value="{{ $remarks }}" placeholder="{{ __('teacher-classroom::app.remarks_placeholder') }}"
                                                       class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-800 outline-none transition focus:border-green-400">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-2xl bg-green-600 px-6 text-sm font-black text-white hover:bg-green-500 transition shadow-sm shadow-green-150">
                                <x-heroicon-o-check class="h-4 w-4" /> {{ __('teacher-classroom::app.save_attendance_date', ['date' => date('d/m/Y', strtotime($selectedDate))]) }}
                            </button>
                        </div>
                    </form>

                    {{-- Attendance History Summary --}}
                    <div class="mt-8 border-t border-slate-100 pt-6">
                        <h3 class="text-sm font-black text-slate-900 mb-3">@lang('teacher-classroom::app.attendance_history')</h3>
                        @if($attendanceHistory->isEmpty())
                            <p class="text-xs font-bold text-slate-400">@lang('teacher-classroom::app.no_attendance_history')</p>
                        @else
                            <div class="overflow-x-auto rounded-2xl border border-slate-200 max-h-[300px] overflow-y-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wide text-slate-400 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-2 bg-slate-50">@lang('teacher-classroom::app.col_session_date')</th>
                                            <th class="px-4 py-2 bg-slate-50">@lang('teacher-classroom::app.col_student')</th>
                                            <th class="px-4 py-2 bg-slate-50">@lang('teacher-classroom::app.status')</th>
                                            <th class="px-4 py-2 bg-slate-50">@lang('teacher-classroom::app.remarks')</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-xs">
                                        @foreach($attendanceHistory->take(50) as $hist)
                                            <tr class="bg-white hover:bg-slate-50/50">
                                                <td class="px-4 py-2 font-black text-slate-700">{{ $hist->session_date->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2 font-bold text-slate-800">{{ $hist->student?->name }}</td>
                                                <td class="px-4 py-2">
                                                    @if($hist->status === 'present')
                                                        <span class="inline-flex rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-black text-green-700">@lang('teacher-classroom::app.present')</span>
                                                    @elseif($hist->status === 'absent')
                                                        <span class="inline-flex rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-black text-red-700">@lang('teacher-classroom::app.absent')</span>
                                                    @elseif($hist->status === 'late')
                                                        <span class="inline-flex rounded-full bg-yellow-50 px-2 py-0.5 text-[10px] font-black text-yellow-700">@lang('teacher-classroom::app.late')</span>
                                                    @elseif($hist->status === 'excused')
                                                        <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-black text-blue-700">@lang('teacher-classroom::app.excused')</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 text-slate-400">{{ $hist->remarks ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Tab 3: Schedule --}}
            <div id="tab-content-schedule" class="tab-content {{ $currentTab === 'schedule' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h2 class="text-base font-black text-slate-900">@lang('teacher-classroom::app.schedule_title')</h2>
                        <p class="text-xs font-bold text-slate-400">@lang('teacher-classroom::app.schedule_subtitle')</p>
                    </div>
                    <button onclick="openModal('schedule-modal')" class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white hover:bg-green-500 transition shadow-sm shadow-green-100">
                        <x-heroicon-o-plus class="h-4 w-4" /> @lang('teacher-classroom::app.add_schedule')
                    </button>
                </div>

                @if($schedules->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-20">
                        <x-heroicon-o-calendar-days class="h-14 w-14 text-slate-200" />
                        <p class="text-base font-black text-slate-600">@lang('teacher-classroom::app.empty_schedule')</p>
                        <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.empty_schedule_desc')</p>
                    </div>
                @else
                    <div class="relative border-l border-slate-200 ml-4 py-2 pl-6 space-y-8">
                        @foreach($schedules as $sched)
                            <div class="relative">
                                <span class="absolute -left-[31px] top-1.5 grid h-4 w-4 place-items-center rounded-full border border-slate-250 bg-white ring-4 ring-slate-100">
                                    <span class="h-2 w-2 rounded-full bg-green-600"></span>
                                </span>
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h4 class="text-sm font-black text-slate-900">{{ $sched->title }}</h4>
                                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                                <span class="flex items-center gap-1">
                                                    <x-heroicon-o-calendar class="h-4 w-4 text-slate-400" />
                                                    {{ $sched->session_date->format('d/m/Y') }}
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <x-heroicon-o-clock class="h-4 w-4 text-slate-400" />
                                                    {{ date('H:i', strtotime($sched->start_time)) }} - {{ date('H:i', strtotime($sched->end_time)) }}
                                                </span>
                                            </div>
                                            @if($sched->description)
                                                <p class="mt-2 text-xs font-semibold text-slate-500 leading-relaxed">{{ $sched->description }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button onclick="openEditScheduleModal({{ json_encode($sched) }})" class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-800">
                                                <x-heroicon-o-pencil class="h-3.5 w-3.5" />
                                            </button>
                                            <form method="POST" action="{{ route('teacher.classrooms.schedules.destroy', $sched) }}"
                                                  data-mindigo-confirm-title="{{ __('teacher-classroom::app.delete_schedule_title') }}"
                                                  data-mindigo-confirm-message="{{ __('teacher-classroom::app.delete_schedule_confirm') }}"
                                                  data-mindigo-confirm-text="{{ __('teacher-classroom::app.delete') }}"
                                                  data-mindigo-confirm-cancel="{{ __('teacher-classroom::app.cancel') }}"
                                                  data-mindigo-confirm-type="danger">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="grid h-8 w-8 place-items-center rounded-lg border border-red-100 text-red-500 hover:bg-red-50">
                                                    <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Tab 4: Announcements --}}
            <div id="tab-content-announcements" class="tab-content {{ $currentTab === 'announcements' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h2 class="text-base font-black text-slate-900">@lang('teacher-classroom::app.announcements_title')</h2>
                        <p class="text-xs font-bold text-slate-400">@lang('teacher-classroom::app.announcements_subtitle')</p>
                    </div>
                    @if(Route::has('teacher.announcements.create'))
                        <a href="{{ route('teacher.announcements.create', ['classroom_id' => $classroom->id]) }}" class="inline-flex h-9 items-center gap-2 rounded-full bg-green-600 px-4 text-xs font-black text-white hover:bg-green-500 transition shadow-sm shadow-green-100">
                            <x-heroicon-o-megaphone class="h-4 w-4" /> @lang('teacher-classroom::app.create_announcement')
                        </a>
                    @endif
                </div>

                @if($announcements->isEmpty())
                    <div class="flex flex-col items-center justify-center gap-3 py-20">
                        <x-heroicon-o-bell class="h-14 w-14 text-slate-200" />
                        <p class="text-base font-black text-slate-600">@lang('teacher-classroom::app.empty_announcements')</p>
                        <p class="text-sm font-bold text-slate-400">@lang('teacher-classroom::app.empty_announcements_desc')</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($announcements as $ann)
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wider
                                                {{ $ann->type === 'warning' ? 'text-red-700 bg-red-50' : '' }}
                                                {{ $ann->type === 'reminder' ? 'text-yellow-700 bg-yellow-50' : '' }}
                                                {{ $ann->type === 'info' ? 'text-blue-700 bg-blue-50' : '' }}
                                                {{ $ann->type === 'assignment' ? 'text-indigo-700 bg-indigo-50' : '' }}">
                                                {{ $ann->type }}
                                            </span>
                                            @if($ann->is_pinned)
                                                <span class="inline-flex items-center text-[10px] text-green-700 font-bold bg-green-50 rounded-full px-2 py-0.5">@lang('teacher-classroom::app.pin')</span>
                                            @endif
                                            <span class="text-xs text-slate-400">{{ $ann->published_at ? $ann->published_at->format('d/m/Y H:i') : __('teacher-classroom::app.draft') }}</span>
                                        </div>
                                        <h4 class="mt-1 text-sm font-black text-slate-900">{{ $ann->title }}</h4>
                                        <p class="mt-1.5 text-xs text-slate-600 leading-relaxed">{{ Str::limit(strip_tags($ann->content), 200) }}</p>
                                    </div>
                                    @if(Route::has('teacher.announcements.show'))
                                        <a href="{{ route('teacher.announcements.show', $ann) }}" class="text-xs font-black text-green-600 hover:text-green-750 whitespace-nowrap">{!! __('teacher-classroom::app.view_detail') !!}</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- MODAL 1: QUẢN LÝ HỌC SINH --}}
<div id="students-modal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur hidden">
    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-black text-slate-900">@lang('teacher-classroom::app.manage_students_title')</h3>
            <button onclick="closeModal('students-modal')" class="text-slate-400 hover:text-slate-600">
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>
        <form method="POST" action="{{ route('teacher.classrooms.students.sync', $classroom) }}" class="flex-1 overflow-y-auto p-6 flex flex-col">
            @csrf
            <p class="text-xs font-bold text-slate-500 mb-4">@lang('teacher-classroom::app.manage_students_desc')</p>
            <div class="grid gap-2 grid-cols-1 sm:grid-cols-2 flex-1 overflow-y-auto border border-slate-100 rounded-2xl p-3 bg-slate-50/50">
                @foreach($allStudents as $student)
                    <label class="flex items-center gap-3 bg-white p-3 rounded-xl border border-slate-150 cursor-pointer hover:bg-green-50/20 hover:border-green-300 transition">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                               @checked($classroom->students->contains('id', $student->id))
                               class="h-4 w-4 accent-green-600">
                        <span class="min-w-0">
                            <span class="block text-xs font-black text-slate-900 truncate">{{ $student->name }}</span>
                            <span class="block text-[10px] font-bold text-slate-400 truncate">{{ $student->email }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeModal('students-modal')" class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 hover:bg-slate-50">@lang('teacher-classroom::app.cancel')</button>
                <button type="submit" class="inline-flex h-9 items-center rounded-xl bg-green-600 px-5 text-xs font-black text-white hover:bg-green-500 shadow-sm shadow-green-100">@lang('teacher-classroom::app.save_list')</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: THÊM/SỬA BUỔI HỌC (SCHEDULE) --}}
<div id="schedule-modal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur hidden">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 id="schedule-modal-title" class="text-base font-black text-slate-900">@lang('teacher-classroom::app.add_schedule_title')</h3>
            <button onclick="closeModal('schedule-modal')" class="text-slate-400 hover:text-slate-600">
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>
        <form id="schedule-form" method="POST" action="{{ route('teacher.classrooms.schedules.store', $classroom) }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="schedule-form-method" value="POST">

            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.schedule_title_field') <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="schedule-title" required placeholder="{{ __('teacher-classroom::app.schedule_title_ph') }}"
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400">
            </div>

            <div class="grid gap-4 grid-cols-3">
                <div class="col-span-1">
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.session_date_field') <span class="text-red-500">*</span></label>
                    <input type="date" name="session_date" id="schedule-date" required
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.start_time_field') <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" id="schedule-start" required
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.end_time_field') <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" id="schedule-end" required
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-black text-slate-600">@lang('teacher-classroom::app.schedule_desc_field')</label>
                <textarea name="description" id="schedule-desc" rows="3" placeholder="{{ __('teacher-classroom::app.schedule_desc_ph') }}"
                          class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 outline-none transition focus:border-green-400 resize-none"></textarea>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeModal('schedule-modal')" class="inline-flex h-9 items-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 hover:bg-slate-50">@lang('teacher-classroom::app.cancel')</button>
                <button type="submit" class="inline-flex h-9 items-center rounded-xl bg-green-600 px-5 text-xs font-black text-white hover:bg-green-500 shadow-sm shadow-green-100">@lang('teacher-classroom::app.save_schedule')</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab switching logic
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        const activeBtn = document.getElementById('tab-btn-' + tabId);
        const activeContent = document.getElementById('tab-content-' + tabId);

        if (activeBtn && activeContent) {
            activeBtn.classList.add('active');
            activeContent.classList.remove('hidden');

            // Save tab state in URL without full reload
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabId);
            window.history.replaceState(null, null, url);
        }
    }

    // Modal management
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Modal management
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Schedule modals logic
    function openEditScheduleModal(sched) {
        document.getElementById('schedule-modal-title').innerText = "{{ __('teacher-classroom::app.edit_schedule_title') }}";
        document.getElementById('schedule-form-method').value = 'PUT';

        // Set form action
        const actionUrl = "{{ route('teacher.classrooms.schedules.update', ':id') }}".replace(':id', sched.id);
        document.getElementById('schedule-form').action = actionUrl;

        // Set inputs
        document.getElementById('schedule-title').value = sched.title;
        // Format date string from MySQL date format if required (YYYY-MM-DD)
        const dateObj = new Date(sched.session_date);
        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        document.getElementById('schedule-date').value = `${yyyy}-${mm}-${dd}`;

        document.getElementById('schedule-start').value = sched.start_time.substring(0, 5);
        document.getElementById('schedule-end').value = sched.end_time.substring(0, 5);
        document.getElementById('schedule-desc').value = sched.description || '';

        openModal('schedule-modal');
    }

    // Reset schedule form when clicking "Add Session"
    window.addEventListener('click', function(e) {
        // If clicking background of modals, close them
        document.querySelectorAll('.modal').forEach(modal => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });

    // Handle schedule form reset on open addition
    const addSchedBtn = document.querySelector('[onclick="openModal(\'schedule-modal\')"]');
    if (addSchedBtn) {
        addSchedBtn.addEventListener('click', function() {
            document.getElementById('schedule-modal-title').innerText = "{{ __('teacher-classroom::app.add_schedule_title') }}";
            document.getElementById('schedule-form-method').value = 'POST';
            document.getElementById('schedule-form').action = "{{ route('teacher.classrooms.schedules.store', $classroom) }}";
            document.getElementById('schedule-title').value = '';
            document.getElementById('schedule-date').value = "{{ now()->toDateString() }}";
            document.getElementById('schedule-start').value = '08:00';
            document.getElementById('schedule-end').value = '10:00';
            document.getElementById('schedule-desc').value = '';
        });
    }
</script>
@endsection
