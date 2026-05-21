@php($editing = isset($managedUser))

<div class="user-form-grid">
    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.name')</span>
        <input name="name" value="{{ old('name', $managedUser->name ?? '') }}" class="user-input" required>
        @error('name')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.email')</span>
        <input type="email" name="email" value="{{ old('email', $managedUser->email ?? '') }}" class="user-input" required>
        @error('email')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.password')</span>
        <input type="password" name="password" class="user-input" @required(!$editing) autocomplete="new-password" placeholder="{{ $editing ? __('Mindigo-user-management::app.password_optional') : '' }}">
        @error('password')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.role')</span>
        <select name="role" class="user-select" required>
            @foreach($roles as $key => $label)
                <option value="{{ $key }}" @selected(old('role', $managedUser->role ?? 'student') === $key)>@lang('Mindigo-user-management::app.roles.' . $key)</option>
            @endforeach
        </select>
        @error('role')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.status')</span>
        <select name="is_active" class="user-select" required>
            <option value="1" @selected((string) old('is_active', (int) ($managedUser->is_active ?? true)) === '1')>@lang('Mindigo-user-management::app.statuses.active')</option>
            <option value="0" @selected((string) old('is_active', (int) ($managedUser->is_active ?? true)) === '0')>@lang('Mindigo-user-management::app.statuses.inactive')</option>
        </select>
        @error('is_active')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.phone')</span>
        <input name="phone" value="{{ old('phone', $managedUser->phone ?? '') }}" class="user-input">
        @error('phone')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.gender')</span>
        <select name="gender" class="user-select">
            <option value="">@lang('Mindigo-user-management::app.not_set')</option>
            @foreach($genders as $key => $label)
                <option value="{{ $key }}" @selected(old('gender', $managedUser->gender ?? '') === $key)>@lang('Mindigo-user-management::app.genders.' . $key)</option>
            @endforeach
        </select>
        @error('gender')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field">
        <span>@lang('Mindigo-user-management::app.date_of_birth')</span>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', isset($managedUser) ? $managedUser->date_of_birth?->format('Y-m-d') : '') }}" class="user-input">
        @error('date_of_birth')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field md:col-span-2">
        <span>@lang('Mindigo-user-management::app.address')</span>
        <input name="address" value="{{ old('address', $managedUser->address ?? '') }}" class="user-input">
        @error('address')<strong>{{ $message }}</strong>@enderror
    </label>

    <label class="user-field md:col-span-2">
        <span>@lang('Mindigo-user-management::app.bio')</span>
        <textarea name="bio" class="user-textarea">{{ old('bio', $managedUser->bio ?? '') }}</textarea>
        @error('bio')<strong>{{ $message }}</strong>@enderror
    </label>
</div>
