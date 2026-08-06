<label>
    <span class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500">{{ $label }}</span>
    <select name="{{ $name }}" class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 outline-none focus:border-green-500">
        <option value="">@lang('teacher-course::catalog.all')</option>
        @foreach($values as $value)
            <option value="{{ $value }}" @selected(($filters[$name] ?? '') === $value)>@lang($translation.'.'.$value)</option>
        @endforeach
    </select>
</label>
