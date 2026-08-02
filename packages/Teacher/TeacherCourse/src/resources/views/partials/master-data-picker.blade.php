@php
    $selectedValue = (string) old($name, $selected ?? '');
    $selectedItem = $items->first(fn ($item) => (string) $item->id === $selectedValue);
@endphp
<div data-course-master-picker class="relative">
    <label class="mb-1.5 block text-xs font-black text-slate-600">{{ $label }}</label>
    <select name="{{ $name }}" data-course-master-select class="sr-only" tabindex="-1" aria-hidden="true">
        <option value="">@lang('teacher-course::app.not_selected')</option>
        @foreach($items as $item)
            <option value="{{ $item->id }}" @selected($selectedValue === (string) $item->id)>{{ $item->name }}</option>
        @endforeach
    </select>
    <button type="button" data-course-master-trigger aria-haspopup="listbox" aria-expanded="false" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 text-left text-sm font-bold text-slate-700 outline-none transition hover:border-green-300 focus:border-green-400 focus:ring-2 focus:ring-green-50">
        <span data-course-master-label class="truncate {{ $selectedItem ? '' : 'text-slate-400' }}">{{ $selectedItem?->name ?? __('teacher-course::app.not_selected') }}</span>
        <x-heroicon-o-chevron-down class="h-4 w-4 shrink-0 text-slate-400" />
    </button>
    <div data-course-master-panel class="absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
        <div class="border-b border-slate-100 p-2">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input type="search" data-course-master-search autocomplete="off" placeholder="{{ $searchPlaceholder }}" class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm font-semibold text-slate-700 outline-none placeholder:text-slate-400 focus:border-green-400 focus:bg-white">
            </div>
        </div>
        <div data-course-master-options role="listbox" class="max-h-52 overflow-y-auto p-1.5">
            <button type="button" data-course-master-option data-value="" data-label="{{ __('teacher-course::app.not_selected') }}" class="flex w-full rounded-lg px-3 py-2.5 text-left text-sm font-bold text-slate-500 hover:bg-green-50 hover:text-green-700">@lang('teacher-course::app.not_selected')</button>
            @foreach($items as $item)
                <button type="button" role="option" data-course-master-option data-value="{{ $item->id }}" data-label="{{ $item->name }}" class="flex w-full rounded-lg px-3 py-2.5 text-left text-sm font-bold text-slate-700 hover:bg-green-50 hover:text-green-700">{{ $item->name }}</button>
            @endforeach
            <p data-course-master-empty class="hidden px-3 py-6 text-center text-sm font-semibold text-slate-400">@lang('teacher-course::app.master_search_empty')</p>
        </div>
    </div>
</div>
