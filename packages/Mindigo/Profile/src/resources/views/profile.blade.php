@extends('Mindigo-dashboard::layouts')

@section('title', 'Tài khoản của tôi — Mindigo')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Profile/src/resources/css/app.css',
        'packages/Mindigo/Profile/src/resources/js/app.js',
    ])
@endsection

@section('content')

{{-- Topbar --}}
<header class="sticky top-0 z-30 bg-white border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-1.5 text-xs text-gray-400 font-bold mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-green-600 transition">@lang('Mindigo-profile::app.dashboard')</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                <span class="text-gray-600">@lang('Mindigo-profile::app.my_account')</span>
            </div>
            <h1 class="text-xl font-black text-gray-900">@lang('Mindigo-profile::app.my_account')</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-black text-gray-500 border border-gray-200 rounded-xl hover:border-green-400 hover:text-green-600 transition">
                @lang('Mindigo-profile::app.cancel')
            </a>
            <button type="submit" form="profile-form" class="flex items-center gap-1.5 px-5 py-2 bg-green-500 hover:bg-green-400 text-white text-sm font-black rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                @lang('Mindigo-profile::app.save_changes')
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="max-w-6xl mx-auto px-6 flex gap-1">
        <button class="profile-tab px-4 py-2.5 text-sm font-black text-green-600 border-b-2 border-green-500 transition" data-tab="ho-so">
            @lang('Mindigo-profile::app.profile')
        </button>
        <button class="profile-tab px-4 py-2.5 text-sm font-bold text-gray-400 border-b-2 border-transparent hover:text-green-600 transition" data-tab="email">
            @lang('Mindigo-profile::app.email_settings')
        </button>
        <button class="profile-tab px-4 py-2.5 text-sm font-bold text-gray-400 border-b-2 border-transparent hover:text-green-600 transition" data-tab="bao-mat">
            @lang('Mindigo-profile::app.security')
        </button>
    </div>
</header>

<div class="max-w-6xl mx-auto px-6 py-8 flex gap-6">

    {{-- Sidebar --}}
    <aside class="w-64 shrink-0">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col items-center text-center gap-3">
            {{-- Avatar --}}
            <div class="relative">
                <div class="w-20 h-20 rounded-full bg-green-500 flex items-center justify-center text-white text-xl font-black overflow-hidden" id="av-preview">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="avatar" class="w-full h-full object-cover"/>
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <label for="avatar-input" class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-green-400 transition shadow-md">
                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </label>
                <input type="file" id="avatar-input" accept="image/*" class="hidden" form="profile-form" name="avatar"/>
            </div>

            <div>
                <div class="font-black text-gray-900 text-base">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-400 font-bold mt-0.5">{{ Auth::user()->role ?? 'Học viên' }}</div>
                <div class="text-xs text-gray-400">{{ Auth::user()->employee_code ?? '' }}</div>
            </div>

            <div class="flex items-center gap-1.5 bg-green-50 text-green-600 text-xs font-black px-3 py-1 rounded-full">
                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                @lang('Mindigo-profile::app.active')
            </div>

            {{-- Meta --}}
            <div class="w-full mt-2 flex flex-col gap-3 text-left">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.32 2 2 0 0 1 3.55 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.54a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <div>
                        <div class="text-xs text-gray-400 font-bold">@lang('Mindigo-profile::app.phone')</div>
                        <div class="text-xs text-gray-700 font-bold">{{ Auth::user()->phone ?? __('Mindigo-profile::app.none') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <div>
                        <div class="text-xs text-gray-400 font-bold">@lang('Mindigo-profile::app.hire_date')</div>
                        <div class="text-xs text-gray-700 font-bold">
                            {{ Auth::user()->hire_date ? Auth::user()->hire_date->format('d/m/Y') : __('Mindigo-profile::app.none') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col gap-5">

        {{-- TAB: Hồ sơ --}}
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

        {{-- TAB: Email --}}
        <div class="profile-tab-panel hidden" id="panel-email">
            <form method="POST" action="{{ route('profile.notifications') }}">
                @csrf
                @method('PUT')
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="text-sm font-black text-gray-900 mb-5 pb-3 border-b border-gray-100">@lang('Mindigo-profile::app.email_notification_settings')</h3>
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-black text-gray-700">@lang('Mindigo-profile::app.receive_attendance_notifications')</label>
                            <select class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                name="notif_attendance">
                                <option value="all" {{ optional($employee->notificationPreference)->notif_attendance === 'all' ? 'selected' : '' }}>@lang('Mindigo-profile::app.all')</option>
                                <option value="daily" {{ optional($employee->notificationPreference)->notif_attendance === 'daily' ? 'selected' : '' }}>@lang('Mindigo-profile::app.daily')</option>
                                <option value="none" {{ optional($employee->notificationPreference)->notif_attendance === 'none' ? 'selected' : '' }}>@lang('Mindigo-profile::app.turn_off')</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-black text-gray-700">@lang('Mindigo-profile::app.receive_payroll_notifications')</label>
                            <select class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                name="notif_payroll">
                                <option value="monthly" {{ optional($employee->notificationPreference)->notif_payroll === 'monthly' ? 'selected' : '' }}>@lang('Mindigo-profile::app.monthly')</option>
                                <option value="none" {{ optional($employee->notificationPreference)->notif_payroll === 'none' ? 'selected' : '' }}>@lang('Mindigo-profile::app.turn_off')</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="submit" class="flex items-center gap-1.5 px-5 py-2.5 bg-green-500 hover:bg-green-400 text-white text-sm font-black rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all">
                            @lang('Mindigo-profile::app.save_settings')
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- TAB: Bảo mật --}}
        <div class="profile-tab-panel hidden" id="panel-bao-mat">
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
                    <h3 class="text-sm font-black text-gray-900 mb-5 pb-3 border-b border-gray-100">@lang('Mindigo-profile::app.change_password')</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5 col-span-2">
                            <label class="text-xs font-black text-gray-700" for="current_password">@lang('Mindigo-profile::app.current_password')</label>
                            <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                type="password" id="current_password" name="current_password" placeholder="••••••••"/>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-black text-gray-700" for="password">@lang('Mindigo-profile::app.new_password')</label>
                            <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                type="password" id="password" name="password" placeholder="••••••••"/>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-black text-gray-700" for="password_confirmation">@lang('Mindigo-profile::app.confirm_password')</label>
                            <input class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                                type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••"/>
                        </div>
                    </div>
                    <div class="mt-5">
                        <button type="submit" class="flex items-center gap-1.5 px-5 py-2.5 bg-green-500 hover:bg-green-400 text-white text-sm font-black rounded-xl shadow-[0_4px_0_#15803d] hover:shadow-[0_2px_0_#15803d] hover:translate-y-0.5 transition-all">
                            @lang('Mindigo-profile::app.update_password')
                        </button>
                    </div>
                </div>
            </form>

            {{-- Danger Zone --}}
            <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
                <h3 class="text-sm font-black text-red-600 mb-5 pb-3 border-b border-red-100">@lang('Mindigo-profile::app.danger_zone')</h3>
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl border border-red-100">
                        <div>
                            <div class="text-sm font-black text-gray-800">@lang('Mindigo-profile::app.suspend_account')</div>
                            <div class="text-xs text-gray-400 mt-0.5">@lang('Mindigo-profile::app.suspend_account_desc')</div>
                        </div>
                        <button type="button" id="btn-suspend-account"
                            class="px-4 py-2 bg-white border border-red-300 text-red-500 text-sm font-black rounded-xl hover:bg-red-500 hover:text-white hover:border-red-500 transition">
                            @lang('Mindigo-profile::app.suspend')
                        </button>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl border border-red-100">
                        <div>
                            <div class="text-sm font-black text-gray-800">@lang('Mindigo-profile::app.delete_account')</div>
                            <div class="text-xs text-gray-400 mt-0.5">@lang('Mindigo-profile::app.delete_account_desc')</div>
                        </div>
                        <button type="button" id="btn-delete-account"
                            class="px-4 py-2 bg-white border border-red-300 text-red-500 text-sm font-black rounded-xl hover:bg-red-500 hover:text-white hover:border-red-500 transition">
                            @lang('Mindigo-profile::app.delete_account')
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
    <script>
        window.__profileSuccess = @json(session('success'));
        window.__profileErrors = @json($errors->all());
    </script>
@endsection