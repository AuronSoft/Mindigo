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