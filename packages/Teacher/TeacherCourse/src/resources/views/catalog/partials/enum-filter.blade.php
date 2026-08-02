<label>
    <span class="mb-1.5 block text-xs font-black text-slate-500">{{ $label }}</span>
    <select name="{{ $name }}" class="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700">
        <option value="">@lang('teacher-course::catalog.all')</option>
        @foreach($values as $value)
            <option value="{{ $value }}" @selected(($filters[$name] ?? '') === $value)>@lang($translation.'.'.$value)</option>
        @endforeach
    </select>
</label>
