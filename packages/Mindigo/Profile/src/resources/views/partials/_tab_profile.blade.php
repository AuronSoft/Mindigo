<div class="profile-tab-panel" id="panel-ho-so">
    <form id="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Thông tin cơ bản --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
            <h3 class="text-sm font-black text-gray-900 mb-5 pb-3 border-b border-gray-100">@lang('Mindigo-profile::app.basic_information')</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="first_name">@lang('Mindigo-profile::app.first_name') *</label>
                    <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        type="text" id="first_name" name="first_name"
                        value="{{ old('first_name', Auth::user()->first_name) }}"
                        placeholder="@lang('Mindigo-profile::app.enter_first_name')" required
                        oninput="this.value = this.value.replace(/[^a-zA-ZÀ-ỹ\s]/g, '')"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="last_name">@lang('Mindigo-profile::app.last_name') *</label>
                    <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        type="text" id="last_name" name="last_name"
                        value="{{ old('last_name', Auth::user()->last_name) }}"
                        placeholder="@lang('Mindigo-profile::app.enter_last_name')" required
                        oninput="this.value = this.value.replace(/[^a-zA-ZÀ-ỹ\s]/g, '')"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="language">@lang('Mindigo-profile::app.language')</label>
                    <select class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        id="language" name="language">
                        <option value="vi" {{ old('language', Auth::user()->language) === 'vi' ? 'selected' : '' }}>@lang('Mindigo-profile::app.vietnamese')</option>
                        <option value="en" {{ old('language', Auth::user()->language) === 'en' ? 'selected' : '' }}>@lang('Mindigo-profile::app.english')</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="phone">@lang('Mindigo-profile::app.phone')</label>
                    <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        type="text" id="phone" name="phone"
                        value="{{ old('phone', Auth::user()->phone) }}"
                        placeholder="@lang('Mindigo-profile::app.phone_placeholder')"
                        pattern="^(\+84|0)\d{9}$" maxlength="12"
                        oninput="this.value = this.value.replace(/[^0-9+]/g, '')"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="date_of_birth">@lang('Mindigo-profile::app.date_of_birth')</label>
                    <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        type="date" id="date_of_birth" name="date_of_birth"
                        value="{{ old('date_of_birth', Auth::user()->date_of_birth ? \Carbon\Carbon::parse(Auth::user()->date_of_birth)->format('Y-m-d') : '') }}"
                        max="{{ now()->subYears(18)->format('Y-m-d') }}"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="gender">@lang('Mindigo-profile::app.gender')</label>
                    <select class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        id="gender" name="gender">
                        <option value="male" {{ old('gender', Auth::user()->gender) === 'male' ? 'selected' : '' }}>@lang('Mindigo-profile::app.male')</option>
                        <option value="female" {{ old('gender', Auth::user()->gender) === 'female' ? 'selected' : '' }}>@lang('Mindigo-profile::app.female')</option>
                        <option value="other" {{ old('gender', Auth::user()->gender) === 'other' ? 'selected' : '' }}>@lang('Mindigo-profile::app.other')</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5 col-span-2">
                    <label class="text-xs font-black text-gray-700" for="address">@lang('Mindigo-profile::app.address')</label>
                    <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        type="text" id="address" name="address"
                        value="{{ old('address', Auth::user()->address) }}"
                        placeholder="@lang('Mindigo-profile::app.enter_address')"/>
                </div>
            </div>
        </div>

        {{-- Thông tin liên hệ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-black text-gray-900 mb-5 pb-3 border-b border-gray-100">@lang('Mindigo-profile::app.contact_additional_information')</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="email_work">@lang('Mindigo-profile::app.work_email')</label>
                    <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-500 bg-gray-50 cursor-not-allowed focus:outline-none"
                        type="email" id="email_work" name="email"
                        value="{{ old('email', Auth::user()->email) }}"
                        readonly required/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-black text-gray-700" for="email_personal">@lang('Mindigo-profile::app.personal_email')</label>
                    <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                        type="email" id="email_personal" name="email_personal"
                        value="{{ old('email_personal', Auth::user()->email_personal) }}"
                        placeholder="@lang('Mindigo-profile::app.personal_email_placeholder')"
                        pattern="^[^@]+@gmail\.com$"/>
                </div>
                <div class="flex flex-col gap-1.5 col-span-2">
                    <label class="text-xs font-black text-gray-700" for="bio">@lang('Mindigo-profile::app.bio')</label>
                    <textarea class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition resize-none h-24"
                        id="bio" name="bio"
                        placeholder="@lang('Mindigo-profile::app.bio_placeholder')">{{ old('bio', Auth::user()->bio) }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>