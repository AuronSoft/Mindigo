<section class="border-t border-green-100 bg-white px-5 py-14 sm:px-8 lg:flex lg:min-h-[calc(100svh-5rem)] lg:items-center lg:py-12">
    <div class="mx-auto w-full max-w-6xl">
        <header class="mx-auto max-w-4xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700">🚀 @lang('core::app.virtual_exam.eyebrow')</span>
            <h2 class="mt-5 text-4xl font-black leading-[1.04] tracking-[-0.045em] text-slate-950 sm:text-5xl lg:text-6xl">
                @lang('core::app.virtual_exam.headline_before')
                <span class="relative inline-block text-green-600">@lang('core::app.virtual_exam.headline_highlight')
                    <svg class="absolute -bottom-2 left-0 h-3 w-full text-green-500" viewBox="0 0 240 12" fill="none" preserveAspectRatio="none" aria-hidden="true"><path d="M3 8.2C55 1.8 154 2.1 237 7.4" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>
                </span>
                @lang('core::app.virtual_exam.headline_after')
            </h2>
            <p class="mx-auto mt-6 max-w-2xl text-sm leading-7 text-slate-500 sm:text-base">@lang('core::app.virtual_exam.description')</p>
            <div class="mx-auto mt-7 flex max-w-2xl flex-col gap-3 rounded-2xl bg-slate-50 p-2 sm:flex-row">
                <label class="sr-only" for="virtual-class-email">@lang('core::app.virtual_exam.email_placeholder')</label>
                <input id="virtual-class-email" type="email" class="min-h-12 flex-1 rounded-xl border-0 bg-white px-5 text-sm outline-none ring-1 ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-green-500" placeholder="@lang('core::app.virtual_exam.email_placeholder')">
                <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-green-500 px-8 text-sm font-black text-white shadow-[0_4px_0_#15803d] transition hover:-translate-y-0.5 hover:bg-green-600">@lang('core::app.virtual_exam.cta')</a>
            </div>
        </header>

        <div class="relative mx-auto mt-10 max-w-5xl">
            <div class="absolute -left-5 top-12 z-10 hidden h-14 w-14 -rotate-12 items-center justify-center rounded-2xl bg-green-600 text-white shadow-xl sm:flex" aria-hidden="true">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none"><path d="M15 10l4.5-2.5v9L15 14M4 7.5h11v9H4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>
            </div>
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_28px_80px_rgba(15,23,42,0.14)]">
                <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <span class="h-3 w-3 rounded-full bg-rose-400"></span><span class="h-3 w-3 rounded-full bg-amber-400"></span><span class="h-3 w-3 rounded-full bg-green-400"></span>
                    <div class="mx-auto rounded-lg border border-slate-200 bg-white px-12 py-1 text-[10px] text-slate-400">app.mindigo.vn/live-class</div>
                </div>
                <div class="grid lg:grid-cols-[1fr_17rem]">
                    <div class="p-4 sm:p-6">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div><h3 class="font-black text-slate-900">@lang('core::app.virtual_exam.class_title')</h3><p class="mt-1 text-xs text-slate-400">@lang('core::app.virtual_exam.class_meta')</p></div>
                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">● @lang('core::app.virtual_exam.recording')</span>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-slate-900">
                            <img src="{{ asset('images/home/virtual-class-teacher.jpg') }}" alt="@lang('core::app.virtual_exam.teacher_alt')" class="h-72 w-full object-cover object-center sm:h-96" loading="lazy">
                            <span class="absolute bottom-4 left-4 rounded-lg bg-slate-950/70 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">@lang('core::app.virtual_exam.teacher_name')</span>
                            <span class="absolute right-4 top-4 rounded-full bg-green-500 px-3 py-1 text-[11px] font-black text-white">LIVE</span>
                        </div>
                        <div class="mt-3 grid grid-cols-4 gap-2">
                            @foreach ([12, 32, 44, 52] as $portrait)
                                <div class="relative overflow-hidden rounded-xl border-2 {{ $loop->last ? 'border-green-500' : 'border-white' }} bg-slate-100 shadow-sm">
                                    <img src="https://randomuser.me/api/portraits/{{ $loop->even ? 'women' : 'men' }}/{{ $portrait }}.jpg" alt="@lang('core::app.virtual_exam.participant_alt')" class="h-20 w-full object-cover sm:h-24" loading="lazy">
                                    <span class="absolute bottom-1.5 left-1.5 rounded bg-slate-950/65 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ __('core::app.virtual_exam.participant_names')[$loop->index] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            @foreach (['microphone', 'camera', 'screen'] as $control)
                                <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600" aria-label="@lang('core::app.virtual_exam.controls.'.$control)"><span class="h-2.5 w-2.5 rounded-full bg-current"></span></button>
                            @endforeach
                            <button type="button" class="rounded-full bg-green-500 px-5 py-2.5 text-xs font-black text-white">@lang('core::app.virtual_exam.share')</button>
                        </div>
                    </div>
                    <aside class="border-t border-slate-100 bg-slate-50/70 p-5 lg:border-l lg:border-t-0">
                        <h3 class="font-black text-slate-900">@lang('core::app.virtual_exam.messages')</h3>
                        <p class="mt-1 text-[11px] leading-5 text-slate-400">@lang('core::app.virtual_exam.messages_desc')</p>
                        <div class="mt-5 space-y-4">
                            @foreach (__('core::app.virtual_exam.chat') as $message)
                                <div class="flex gap-2.5"><img src="https://randomuser.me/api/portraits/{{ $loop->even ? 'women' : 'men' }}/{{ 20 + $loop->index * 9 }}.jpg" alt="" class="h-8 w-8 rounded-full object-cover"><div><div class="flex items-center gap-2"><strong class="text-[11px] text-slate-800">{{ $message['name'] }}</strong><span class="text-[9px] text-slate-400">{{ $message['time'] }}</span></div><p class="mt-1 text-[10px] leading-4 text-slate-500">{{ $message['message'] }}</p></div></div>
                            @endforeach
                        </div>
                        <div class="mt-5 flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-2"><span class="flex-1 px-2 text-[10px] text-slate-400">@lang('core::app.virtual_exam.message_placeholder')</span><button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500 font-black text-white" aria-label="@lang('core::app.virtual_exam.send')">→</button></div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</section>
