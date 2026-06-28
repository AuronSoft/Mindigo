@extends('Mindigo-dashboard::layouts')
@section('title', __('student-notebook::app.title') . ' · Mindigo LMS')
@section('meta_description', __('student-notebook::app.subtitle'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
<div class="flex min-h-screen flex-col bg-slate-50">

    {{-- Header --}}
    <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white/90 px-6 py-4 backdrop-blur">
        <div>
            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">@lang('student-notebook::app.area')</p>
            <h1 class="mt-0.5 text-lg font-black text-slate-950">@lang('student-notebook::app.title')</h1>
            <p class="text-xs font-semibold text-slate-400">@lang('student-notebook::app.subtitle')</p>
        </div>
        <a href="{{ route('student.notebook.index', ['note' => 'new']) }}"
            class="inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-5 text-sm font-black text-white no-underline shadow-sm shadow-green-200 transition hover:bg-green-500">
            <x-heroicon-o-plus class="h-4 w-4" />
            @lang('student-notebook::app.new_note')
        </a>
    </header>

    <div class="grid flex-1 grid-cols-1 gap-5 p-6 lg:grid-cols-[20rem_minmax(0,1fr)]">

        {{-- ── Danh sách ghi chú ── --}}
        <aside class="flex flex-col gap-3">
            <p class="px-1 text-xs font-black uppercase tracking-wider text-slate-400">
                {{ __('student-notebook::app.count_label', ['count' => $notes->count()]) }}
            </p>

            <div class="flex flex-col gap-2">
                @forelse($notes as $note)
                    @php $isActive = $selected && $selected->id === $note->id; @endphp
                    <a href="{{ route('student.notebook.index', ['note' => $note->id]) }}"
                       class="group rounded-2xl border bg-white p-4 no-underline shadow-sm transition hover:border-green-200 {{ $isActive ? 'border-green-300 ring-1 ring-green-200' : 'border-slate-200' }}">
                        <p class="truncate font-black text-slate-800 {{ $isActive ? 'text-green-700' : '' }}">
                            {{ $note->title ?: __('student-notebook::app.untitled') }}
                        </p>
                        @if($note->content)
                            <p class="mt-1 line-clamp-2 text-xs font-semibold leading-relaxed text-slate-400">{{ $note->content }}</p>
                        @endif
                        <p class="mt-2 text-[10px] font-bold uppercase tracking-wide text-slate-300">
                            {{ __('student-notebook::app.updated_at', ['time' => $note->updated_at?->diffForHumans()]) }}
                        </p>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-10 text-center">
                        <p class="text-sm font-bold text-slate-400">@lang('student-notebook::app.empty_list')</p>
                    </div>
                @endforelse
            </div>
        </aside>

        {{-- ── Trình soạn thảo ── --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @if($creating || $selected)
                @php $isEdit = (bool) $selected; @endphp
                <form action="{{ $isEdit ? route('student.notebook.update', $selected) : route('student.notebook.store') }}" method="POST" class="flex h-full flex-col gap-4">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-400">
                            {{ $isEdit ? __('student-notebook::app.editor_edit') : __('student-notebook::app.editor_new') }}
                        </h2>
                        @if($isEdit)
                            <span class="text-[11px] font-bold text-slate-400">
                                {{ __('student-notebook::app.updated_at', ['time' => $selected->updated_at?->diffForHumans()]) }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-bold text-slate-700">@lang('student-notebook::app.field_title') <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $selected->title ?? '') }}"
                               class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50"
                               placeholder="{{ __('student-notebook::app.title_placeholder') }}" autofocus>
                        @error('title')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-1 flex-col space-y-1">
                        <label class="text-sm font-bold text-slate-700">@lang('student-notebook::app.field_content')</label>
                        <textarea name="content" rows="14"
                                  class="block w-full flex-1 resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold leading-relaxed text-slate-700 outline-none focus:border-green-300 focus:ring-2 focus:ring-green-50"
                                  placeholder="{{ __('student-notebook::app.content_placeholder') }}">{{ old('content', $selected->content ?? '') }}</textarea>
                        @error('content')<p class="text-xs font-semibold text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <div>
                            @if($isEdit)
                                <button type="submit" form="delete-note-form"
                                        class="inline-flex h-10 items-center gap-2 rounded-full border border-red-100 bg-red-50 px-4 text-sm font-black text-red-600 transition hover:bg-red-100">
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                    @lang('student-notebook::app.delete')
                                </button>
                            @endif
                        </div>
                        <button type="submit"
                                class="inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white shadow-sm shadow-green-200 transition hover:bg-green-500">
                            <x-heroicon-o-check class="h-4 w-4" />
                            @lang('student-notebook::app.save')
                        </button>
                    </div>
                </form>

                @if($selected)
                    <form id="delete-note-form" action="{{ route('student.notebook.destroy', $selected) }}" method="POST" class="hidden"
                          data-mindigo-confirm-title="{{ __('student-notebook::app.delete_confirm_title') }}"
                          data-mindigo-confirm-message="{{ __('student-notebook::app.delete_confirm_message') }}"
                          data-mindigo-confirm-text="{{ __('student-notebook::app.delete') }}"
                          data-mindigo-confirm-cancel="{{ __('student-notebook::app.cancel') }}"
                          data-mindigo-confirm-type="danger">
                        @csrf @method('DELETE')
                    </form>
                @endif
            @else
                <div class="flex h-full min-h-75 flex-col items-center justify-center gap-4 text-center">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-slate-50 text-slate-300">
                        <x-heroicon-o-book-open class="h-10 w-10" />
                    </span>
                    <div>
                        <p class="text-lg font-black text-slate-700">@lang('student-notebook::app.empty_title')</p>
                        <p class="mt-1 max-w-xs text-sm font-semibold leading-relaxed text-slate-400">@lang('student-notebook::app.empty_desc')</p>
                    </div>
                    <a href="{{ route('student.notebook.index', ['note' => 'new']) }}"
                       class="mt-2 inline-flex h-10 items-center gap-2 rounded-full bg-green-600 px-6 text-sm font-black text-white no-underline shadow-sm transition hover:bg-green-500">
                        <x-heroicon-o-plus class="h-4 w-4" /> @lang('student-notebook::app.new_note')
                    </a>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
