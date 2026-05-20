@php($preferences = $user->notificationPreference)

<div class="profile-tab-panel hidden" id="panel-email">
    <form method="POST" action="{{ route('profile.notifications') }}">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="text-base font-black text-slate-950">@lang('Mindigo-profile::app.email_notification_settings')</h3>
                <p class="mt-1 text-sm font-semibold text-slate-500">@lang('Mindigo-profile::app.email_notification_desc')</p>
            </div>

            <div class="space-y-3">
                <label class="flex cursor-pointer items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-green-200 hover:bg-green-50/60">
                    <span>
                        <span class="block text-sm font-black text-slate-900">@lang('Mindigo-profile::app.notif_new_quiz')</span>
                        <span class="mt-1 block text-sm font-semibold leading-6 text-slate-500">@lang('Mindigo-profile::app.notif_new_quiz_desc')</span>
                    </span>
                    <input type="hidden" name="notif_new_quiz" value="0">
                    <input type="checkbox" name="notif_new_quiz" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                        {{ old('notif_new_quiz', $preferences?->notif_new_quiz ?? true) ? 'checked' : '' }}>
                </label>

                <label class="flex cursor-pointer items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-green-200 hover:bg-green-50/60">
                    <span>
                        <span class="block text-sm font-black text-slate-900">@lang('Mindigo-profile::app.notif_system_news')</span>
                        <span class="mt-1 block text-sm font-semibold leading-6 text-slate-500">@lang('Mindigo-profile::app.notif_system_news_desc')</span>
                    </span>
                    <input type="hidden" name="notif_system_news" value="0">
                    <input type="checkbox" name="notif_system_news" value="1" class="mt-1 h-5 w-5 rounded border-slate-300 text-green-600 focus:ring-green-500"
                        {{ old('notif_system_news', $preferences?->notif_system_news ?? true) ? 'checked' : '' }}>
                </label>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-green-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                    @lang('Mindigo-profile::app.save_settings')
                </button>
            </div>
        </section>
    </form>
</div>
