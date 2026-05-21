@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-question-bank::app.title'))

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
        'packages/Mindigo/QuestionBank/src/resources/css/app.css',
        'packages/Mindigo/QuestionBank/src/resources/js/app.js',
    ])
@endsection

@section('content')
    <div class="question-page mx-auto flex max-w-7xl flex-col gap-6">
        <header class="question-hero">
            <div class="min-w-0">
                <div class="question-breadcrumb">
                    <a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a>
                    <span>/</span>
                    <strong>@lang('Mindigo-question-bank::app.breadcrumb')</strong>
                </div>
                <h1>@lang('Mindigo-question-bank::app.heading')</h1>
                <p>@lang('Mindigo-question-bank::app.description')</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="question-secondary-button" data-import-toggle>
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8 12 3 7 8"/><path d="M12 3v12"/></svg>
                    @lang('Mindigo-question-bank::app.import_questions')
                </button>
                <button type="button" class="question-secondary-button" data-folder-toggle>
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7l-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                    @lang('Mindigo-question-bank::app.create_folder')
                </button>
                <a href="{{ route('question-bank.create') }}" class="question-primary-button">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    @lang('Mindigo-question-bank::app.create_question')
                </a>
            </div>
        </header>

        <form method="POST" action="{{ route('question-bank.folders.store') }}" class="question-folder-form hidden" data-folder-form data-mindigo-confirm-title="@lang('Mindigo-question-bank::app.confirm_folder_title')" data-mindigo-confirm-message="@lang('Mindigo-question-bank::app.confirm_folder_message')" data-mindigo-confirm-text="@lang('Mindigo-question-bank::app.create_folder')" data-mindigo-confirm-cancel="@lang('Mindigo-question-bank::app.cancel')">
            @csrf
            <input name="name" class="question-input" placeholder="@lang('Mindigo-question-bank::app.folder_name')" required>
            <input name="subject" class="question-input" placeholder="@lang('Mindigo-question-bank::app.subject')">
            <select name="color" class="question-select">
                @foreach(['green', 'sky', 'amber', 'rose', 'slate'] as $color)
                    <option value="{{ $color }}">@lang('Mindigo-question-bank::app.folder_colors.' . $color)</option>
                @endforeach
            </select>
            <input name="description" class="question-input md:col-span-2" placeholder="@lang('Mindigo-question-bank::app.folder_description')">
            <button type="submit" class="question-filter-button">@lang('Mindigo-question-bank::app.create_folder')</button>
        </form>

        <form method="POST" action="{{ route('question-bank.import') }}" enctype="multipart/form-data" class="question-import-panel hidden" data-import-form data-mindigo-confirm-title="@lang('Mindigo-question-bank::app.confirm_import_title')" data-mindigo-confirm-message="@lang('Mindigo-question-bank::app.confirm_import_message')" data-mindigo-confirm-text="@lang('Mindigo-question-bank::app.import_questions')" data-mindigo-confirm-cancel="@lang('Mindigo-question-bank::app.cancel')">
            @csrf
            <label class="question-import-drop" data-import-drop>
                <input type="file" name="import_file" accept=".csv,.txt,.json" required data-import-file>
                <span class="question-import-icon">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8 12 3 7 8"/><path d="M12 3v12"/></svg>
                </span>
                <span>
                    <strong>@lang('Mindigo-question-bank::app.import_drop_title')</strong>
                    <small data-import-file-name>@lang('Mindigo-question-bank::app.import_drop_desc')</small>
                </span>
            </label>
            <div class="question-import-controls">
                <select name="folder_id" class="question-select">
                    <option value="">@lang('Mindigo-question-bank::app.import_folder_from_file')</option>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" @selected((string) $currentFolderId === (string) $folder->id)>{{ $folder->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="question-select">
                    <option value="draft">@lang('Mindigo-question-bank::app.statuses.draft')</option>
                    <option value="reviewing">@lang('Mindigo-question-bank::app.statuses.reviewing')</option>
                </select>
                <button type="submit" class="question-primary-button">@lang('Mindigo-question-bank::app.import_questions')</button>
            </div>
            <div class="question-import-format">
                <strong>@lang('Mindigo-question-bank::app.import_format_title')</strong>
                <code>folder,subject,topic,type,difficulty,content,options,correct_answers,explanation,tags</code>
                <span>@lang('Mindigo-question-bank::app.import_format_desc')</span>
            </div>
        </form>

        <section class="grid gap-4 md:grid-cols-4">
            @foreach(['total' => 'total_questions', 'approved' => 'approved_questions', 'reviewing' => 'reviewing_questions', 'draft' => 'draft_questions'] as $key => $label)
                <article class="question-stat-card">
                    <span>@lang('Mindigo-question-bank::app.' . $label)</span>
                    <strong>{{ number_format($stats[$key] ?? 0) }}</strong>
                </article>
            @endforeach
        </section>

        <div class="question-bank-layout">
            <aside class="question-folder-panel">
                <a href="{{ route('question-bank.index', request()->except('folder_id', 'page')) }}" class="question-folder-item {{ blank($currentFolderId) ? 'is-active' : '' }}">
                    <span class="question-folder-dot bg-green-500"></span>
                    <span class="min-w-0 flex-1">
                        <strong>@lang('Mindigo-question-bank::app.all_questions')</strong>
                        <small>{{ number_format($stats['total'] ?? 0) }} @lang('Mindigo-question-bank::app.questions_count')</small>
                    </span>
                </a>
                <a href="{{ route('question-bank.index', array_merge(request()->except('page'), ['folder_id' => 'none'])) }}" class="question-folder-item {{ $currentFolderId === 'none' ? 'is-active' : '' }}">
                    <span class="question-folder-dot bg-slate-400"></span>
                    <span class="min-w-0 flex-1">
                        <strong>@lang('Mindigo-question-bank::app.no_folder')</strong>
                        <small>@lang('Mindigo-question-bank::app.unsorted_questions')</small>
                    </span>
                </a>
                @foreach($folders as $folder)
                    <a href="{{ route('question-bank.index', array_merge(request()->except('page'), ['folder_id' => $folder->id])) }}" class="question-folder-item {{ (string) $currentFolderId === (string) $folder->id ? 'is-active' : '' }}">
                        <span class="question-folder-dot question-folder-dot-{{ $folder->color }}"></span>
                        <span class="min-w-0 flex-1">
                            <strong>{{ $folder->name }}</strong>
                            <small>{{ number_format($folder->questions_count) }} @lang('Mindigo-question-bank::app.questions_count')</small>
                        </span>
                    </a>
                @endforeach
            </aside>

            <div class="min-w-0 flex flex-col gap-6">
        <form method="GET" action="{{ route('question-bank.index') }}" class="question-filter">
            @if(filled($currentFolderId))
                <input type="hidden" name="folder_id" value="{{ $currentFolderId }}">
            @endif
            <input name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="@lang('Mindigo-question-bank::app.search_placeholder')" class="question-input">
            <input name="subject" value="{{ $filters['subject'] ?? '' }}" placeholder="@lang('Mindigo-question-bank::app.subject')" class="question-input" list="question-subject-filter">
            <datalist id="question-subject-filter">
                @foreach($subjects as $subject)
                    <option value="{{ $subject }}"></option>
                @endforeach
            </datalist>
            <select name="status" class="question-select">
                <option value="">@lang('Mindigo-question-bank::app.all_statuses')</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>@lang('Mindigo-question-bank::app.statuses.' . $status)</option>
                @endforeach
            </select>
            <select name="difficulty" class="question-select">
                <option value="">@lang('Mindigo-question-bank::app.all_difficulties')</option>
                @foreach($difficulties as $difficulty)
                    <option value="{{ $difficulty }}" @selected(($filters['difficulty'] ?? '') === $difficulty)>@lang('Mindigo-question-bank::app.difficulties.' . $difficulty)</option>
                @endforeach
            </select>
            <button type="submit" class="question-filter-button">@lang('Mindigo-question-bank::app.filter')</button>
            <a href="{{ route('question-bank.index') }}" class="question-secondary-button">@lang('Mindigo-question-bank::app.reset')</a>
        </form>

        <section class="question-card overflow-hidden">
            @if($questions->count())
                <div class="overflow-x-auto">
                    <table class="question-table">
                        <thead>
                            <tr>
                                <th>@lang('Mindigo-question-bank::app.question')</th>
                                <th>@lang('Mindigo-question-bank::app.subject')</th>
                                <th>@lang('Mindigo-question-bank::app.type')</th>
                                <th>@lang('Mindigo-question-bank::app.status')</th>
                                <th>@lang('Mindigo-question-bank::app.creator')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questions as $question)
                                <tr>
                                    <td>
                                        <a href="{{ route('question-bank.show', $question) }}" class="block max-w-xl truncate text-sm font-black text-slate-900 no-underline hover:text-green-700">{{ $question->content }}</a>
                                        <span class="mt-1 block text-xs font-semibold text-slate-400">
                                            {{ $question->folder?->name ?: __('Mindigo-question-bank::app.no_folder') }} / {{ $question->topic ?: __('Mindigo-question-bank::app.no_topic') }}
                                        </span>
                                    </td>
                                    <td class="text-sm font-black text-slate-700">{{ $question->subject }}</td>
                                    <td><span class="question-badge question-type">@lang('Mindigo-question-bank::app.types.' . $question->type)</span></td>
                                    <td><span class="question-badge question-status-{{ $question->status }}">@lang('Mindigo-question-bank::app.statuses.' . $question->status)</span></td>
                                    <td class="text-sm font-bold text-slate-500">{{ $question->creator?->name ?: '-' }}</td>
                                    <td>
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('question-bank.edit', $question) }}" class="question-secondary-button">@lang('Mindigo-question-bank::app.edit')</a>
                                            <form method="POST" action="{{ route('question-bank.destroy', $question) }}" data-mindigo-confirm-title="@lang('Mindigo-question-bank::app.confirm_delete_title')" data-mindigo-confirm-message="@lang('Mindigo-question-bank::app.confirm_delete_message')" data-mindigo-confirm-text="@lang('Mindigo-question-bank::app.delete')" data-mindigo-confirm-cancel="@lang('Mindigo-question-bank::app.cancel')" data-mindigo-confirm-type="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="question-danger-button">@lang('Mindigo-question-bank::app.delete')</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4">{{ $questions->links() }}</div>
            @else
                <div class="p-6">
                    @include('core::partials.empty-state', [
                        'title' => __('Mindigo-question-bank::app.empty_title'),
                        'message' => __('Mindigo-question-bank::app.empty_desc'),
                    ])
                </div>
            @endif
        </section>
            </div>
        </div>
    </div>
@endsection
