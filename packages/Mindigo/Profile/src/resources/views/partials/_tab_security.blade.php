<div class="profile-tab-panel hidden" id="panel-bao-mat">
    <form method="POST" action="{{ route('profile.password') }}">
        @csrf
        @method('PUT')

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70">
            <div class="mb-5 border-b border-slate-100 pb-4">
                <h3 class="text-base font-black text-slate-950">@lang('Mindigo-profile::app.change_password')</h3>
                <p class="mt-1 text-sm font-semibold text-slate-500">@lang('Mindigo-profile::app.change_password_desc')</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="current_password">@lang('Mindigo-profile::app.current_password')</label>
                    <input class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        type="password" id="current_password" name="current_password" placeholder="********">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="password">@lang('Mindigo-profile::app.new_password')</label>
                    <input class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        type="password" id="password" name="password" placeholder="********">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-black text-slate-700" for="password_confirmation">@lang('Mindigo-profile::app.confirm_password')</label>
                    <input class="min-h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-green-400 focus:ring-4 focus:ring-green-100"
                        type="password" id="password_confirmation" name="password_confirmation" placeholder="********">
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-green-600 px-5 text-sm font-black text-white shadow-sm transition hover:bg-green-500">
                    @lang('Mindigo-profile::app.update_password')
                </button>
            </div>
        </section>
    </form>

    <section class="mt-5 rounded-2xl border border-red-100 bg-white p-5 shadow-sm shadow-red-100/60">
        <div class="mb-5 border-b border-red-100 pb-4">
            <h3 class="text-base font-black text-red-600">@lang('Mindigo-profile::app.danger_zone')</h3>
            <p class="mt-1 text-sm font-semibold text-slate-500">@lang('Mindigo-profile::app.danger_zone_desc')</p>
        </div>

        <div class="space-y-3">
            <div class="flex flex-col gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-black text-slate-900">@lang('Mindigo-profile::app.suspend_account')</div>
                    <div class="mt-1 text-sm font-semibold text-slate-500">@lang('Mindigo-profile::app.suspend_account_desc')</div>
                </div>
                <button type="button" id="btn-suspend-account" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-red-200 bg-white px-4 text-sm font-black text-red-600 transition hover:bg-red-600 hover:text-white">
                    @lang('Mindigo-profile::app.suspend')
                </button>
            </div>

            <div class="flex flex-col gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-black text-slate-900">@lang('Mindigo-profile::app.delete_account')</div>
                    <div class="mt-1 text-sm font-semibold text-slate-500">@lang('Mindigo-profile::app.delete_account_desc')</div>
                </div>
                <button type="button" id="btn-delete-account" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-red-200 bg-white px-4 text-sm font-black text-red-600 transition hover:bg-red-600 hover:text-white">
                    @lang('Mindigo-profile::app.delete_account')
                </button>
            </div>
        </div>
    </section>
</div>
