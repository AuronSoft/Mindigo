@php
    $subject = $subject ?? null;
@endphp

<section class="subject-card p-5">
    <div class="subject-section-head">
        <span>01</span>
        <div>
            <h2>@lang('Mindigo-subject-management::app.basic_info')</h2>
            <p>@lang('Mindigo-subject-management::app.basic_info_desc')</p>
        </div>
    </div>

    <div class="subject-form-grid mt-5">
        <label class="subject-field">
            <span>@lang('Mindigo-subject-management::app.name')</span>
            <input name="name" value="{{ old('name', $subject?->name) }}" class="subject-input" required>
            @error('name')<strong>{{ $message }}</strong>@enderror
        </label>

        <label class="subject-field">
            <span>@lang('Mindigo-subject-management::app.code')</span>
            <input name="code" value="{{ old('code', $subject?->code) }}" class="subject-input" required>
            @error('code')<strong>{{ $message }}</strong>@enderror
        </label>

        <label class="subject-field">
            <span>@lang('Mindigo-subject-management::app.status')</span>
            <select name="status" class="subject-select">
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $subject?->status ?? 'active') === $status)>@lang('Mindigo-subject-management::app.statuses.' . $status)</option>
                @endforeach
            </select>
            @error('status')<strong>{{ $message }}</strong>@enderror
        </label>

        <label class="subject-field">
            <span>@lang('Mindigo-subject-management::app.sort_order')</span>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $subject?->sort_order ?? 0) }}" class="subject-input" required>
            @error('sort_order')<strong>{{ $message }}</strong>@enderror
        </label>

        <label class="subject-field">
            <span>@lang('Mindigo-subject-management::app.color')</span>
            <select name="color" class="subject-select">
                @foreach($colors as $color)
                    <option value="{{ $color }}" @selected(old('color', $subject?->color ?? 'green') === $color)>@lang('Mindigo-subject-management::app.colors.' . $color)</option>
                @endforeach
            </select>
            @error('color')<strong>{{ $message }}</strong>@enderror
        </label>

        <label class="subject-field">
            <span>@lang('Mindigo-subject-management::app.icon')</span>
            <input name="icon" value="{{ old('icon', $subject?->icon) }}" class="subject-input" placeholder="book-open">
            @error('icon')<strong>{{ $message }}</strong>@enderror
        </label>

        <label class="subject-field md:col-span-2">
            <span>@lang('Mindigo-subject-management::app.description_field')</span>
            <textarea name="description" class="subject-textarea">{{ old('description', $subject?->description) }}</textarea>
            @error('description')<strong>{{ $message }}</strong>@enderror
        </label>
    </div>
</section>
