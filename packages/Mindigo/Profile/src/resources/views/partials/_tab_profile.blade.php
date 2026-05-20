<div class="profile-tab-panel" id="panel-ho-so">
    <form id="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-black text-slate-950">@lang('Mindigo-profile::app.basic_information')</h3>
                    <p class="mt-1 text-sm font-semibold text-slate-500">@lang('Mindigo-profile::app.basic_information_desc')</p>
                </div>
                <span class="rounded-full bg-slate-50 px-3 py-1 text-xs font-black text-slate-500 ring-1 ring-slate-100">
                    {{ $roleProfile['label'] }}
                </span>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="name">@lang('Mindigo-profile::app.full_name') *</label>
                    <input class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                        placeholder="@lang('Mindigo-profile::app.enter_full_name')" required>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="email">@lang('Mindigo-profile::app.registered_email')</label>
                    <input class="min-h-11 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-500 outline-none"
                        type="email" id="email" value="{{ $user->email }}" readonly>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="phone">@lang('Mindigo-profile::app.phone')</label>
                    <input class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                        placeholder="@lang('Mindigo-profile::app.phone_placeholder')" maxlength="12">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="date_of_birth">@lang('Mindigo-profile::app.date_of_birth')</label>
                    <input class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        type="date" id="date_of_birth" name="date_of_birth"
                        value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                        max="{{ now()->subDay()->format('Y-m-d') }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="gender">@lang('Mindigo-profile::app.gender')</label>
                    <select class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        id="gender" name="gender">
                        <option value="">@lang('Mindigo-profile::app.select_gender')</option>
                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>@lang('Mindigo-profile::app.male')</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>@lang('Mindigo-profile::app.female')</option>
                        <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>@lang('Mindigo-profile::app.other')</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="address">@lang('Mindigo-profile::app.address')</label>
                    <input class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        type="text" id="address" name="address" value="{{ old('address', $user->address) }}"
                        placeholder="@lang('Mindigo-profile::app.enter_address')">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="bio">@lang('Mindigo-profile::app.bio')</label>
                    <textarea class="min-h-28 w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        id="bio" name="bio" placeholder="@lang('Mindigo-profile::app.bio_placeholder')">{{ old('bio', $user->bio) }}</textarea>
                </div>
            </div>
        </section>
    </form>
</div>
