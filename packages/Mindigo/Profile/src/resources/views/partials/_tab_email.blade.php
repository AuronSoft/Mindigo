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