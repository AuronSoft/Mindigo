{{-- Feature section --}}
<section id="features" class="scroll-mt-20 border-t border-green-100 bg-green-50 px-6 py-16 lg:flex lg:min-h-[calc(100svh-5rem)] lg:items-center lg:px-10 lg:py-12">
    <div class="mx-auto w-full max-w-7xl">
        <div class="mb-10 text-center lg:mb-12">
            <p class="text-3xl font-black text-green-600">@lang('core::app.feature.section_title')</p>
        </div>

        <div class="flex flex-col items-center gap-16 lg:flex-row lg:gap-20">
            <div class="flex flex-1 flex-col gap-6">
                <span class="w-fit rounded-lg bg-green-500 px-3 py-1 text-xs font-black text-white">@lang('core::app.feature.badge')</span>
                <h2 class="text-[2.65rem] font-black leading-[1.02] tracking-[-0.045em] text-slate-950 sm:text-[3.2rem] lg:text-[3.65rem]">
                    <span class="text-green-600">@lang('core::app.feature.heading_highlight')</span> @lang('core::app.feature.heading_rest')
                </h2>
                <div class="flex flex-col gap-5">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16" aria-hidden="true"><rect x="1" y="2" width="14" height="12" rx="2" stroke="#16a34a" stroke-width="1.3"/><path d="M4 7h8M4 10h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-500">@lang('core::app.feature.feature_1')</p>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1v5M8 10v5M1 8h5M10 8h5" stroke="#16a34a" stroke-width="1.3" stroke-linecap="round"/></svg>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-500">@lang('core::app.feature.feature_2')</p>
                    </div>
                </div>
                <a href="#" class="w-fit rounded-xl bg-green-500 px-7 py-3.5 text-sm font-black text-white shadow-[0_4px_0_#15803d] transition-all hover:translate-y-0.5 hover:bg-green-400 hover:shadow-[0_2px_0_#15803d]">
                    @lang('core::app.feature.cta')
                </a>
            </div>

            {{-- Product workspace preview --}}
            <div class="group relative w-full flex-1 pb-24 sm:px-8 lg:px-0">
                <div class="pointer-events-none absolute -right-3 top-8 h-32 w-32 rounded-full bg-green-200/45 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-3 left-8 h-28 w-28 rounded-full bg-violet-300/25 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -top-4 right-16 z-20 grid h-12 w-12 place-items-center rounded-2xl border border-violet-100 bg-linear-to-br from-blue-50 via-violet-50 to-amber-50 shadow-lg" style="animation: floatStar 3.4s ease-in-out infinite" aria-hidden="true">
                    <svg class="h-7 w-7 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9.75 3.75 8.6 7.2a2.25 2.25 0 0 1-1.42 1.42L3.75 9.75l3.43 1.14a2.25 2.25 0 0 1 1.42 1.42l1.15 3.44 1.14-3.44a2.25 2.25 0 0 1 1.42-1.42l3.44-1.14-3.44-1.15a2.25 2.25 0 0 1-1.42-1.42L9.75 3.75Zm7.5 10.5-.58 1.72a1.13 1.13 0 0 1-.7.7l-1.72.58 1.72.57c.33.11.6.38.7.71l.58 1.72.57-1.72c.11-.33.38-.6.71-.7l1.72-.58-1.72-.57a1.13 1.13 0 0 1-.7-.71l-.58-1.72Z"/></svg>
                </div>
                <div class="pointer-events-none absolute -right-2 top-24 z-30 hidden items-center gap-2 rounded-xl border border-green-100 bg-white px-3 py-2 shadow-lg sm:flex" style="animation: floatBadge 3.8s .5s ease-in-out infinite" aria-hidden="true">
                    <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-60"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-600"></span></span>
                    <span class="text-[9px] font-black text-slate-700">@lang('core::app.ai_card.ai_ready')</span>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_24px_60px_rgba(15,23,42,0.12)] transition duration-500 ease-out group-hover:-translate-y-1 group-hover:shadow-[0_30px_72px_rgba(21,128,61,0.16)]">
                    <div class="flex h-12 items-center gap-3 border-b border-slate-200 bg-slate-50 px-4">
                        <div class="flex gap-1.5" aria-hidden="true">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                        </div>
                        <div class="flex min-w-0 flex-1 items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5">
                            <svg class="mr-2 h-3.5 w-3.5 shrink-0 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                            <span class="truncate text-[10px] font-semibold text-slate-400">@lang('core::app.nav.url_display')</span>
                        </div>
                        <span class="hidden rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[9px] font-bold text-slate-600 sm:block">@lang('core::app.nav.username')</span>
                    </div>

                    <div class="border-b border-slate-200 px-4 py-3 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-wider text-green-700">@lang('core::app.ai_card.tab_input')</p>
                            <p class="mt-0.5 text-xs font-black text-slate-900">@lang('core::app.ai_card.section_1_name')</p>
                        </div>
                        <div class="mt-2 flex gap-2 sm:mt-0">
                            <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-[9px] font-bold text-slate-600">@lang('core::app.ai_card.btn_return')</span>
                            <span class="ai-save-button relative overflow-hidden rounded-lg bg-green-500 px-3 py-1.5 text-[9px] font-black text-white shadow-[0_2px_0_#15803d]"><span class="ai-save-label">@lang('core::app.ai_card.btn_save')</span><span class="ai-save-check absolute inset-0 grid place-items-center">✓</span></span>
                        </div>
                    </div>

                    <div class="grid min-h-80 grid-cols-[112px_minmax(0,1fr)] sm:grid-cols-[150px_minmax(0,1fr)]">
                        <aside class="border-r border-slate-200 bg-slate-50 p-3">
                            <p class="mb-2 text-[8px] font-black uppercase tracking-wide text-slate-400">@lang('core::app.ai_card.section_list_label')</p>
                            <div class="rounded-lg bg-green-500 px-2.5 py-2 text-[9px] font-black text-white shadow-[0_1px_0_#15803d]">@lang('core::app.ai_card.section_1_name')</div>
                            <div class="mt-1 rounded-lg px-2.5 py-2 text-[9px] font-semibold text-slate-500">@lang('core::app.exam.section_2_title')</div>
                            <div class="mt-5 border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between text-[8px] font-bold text-slate-500"><span>@lang('core::app.ai_card.question_index_label')</span><span class="text-green-700">30%</span></div>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200"><div class="h-full w-[30%] rounded-full bg-green-500"></div></div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-1.5">
                                @foreach(range(1, 8) as $question)
                                    <span class="grid h-5 place-items-center rounded-md text-[7px] font-black {{ $question <= 3 ? 'bg-green-700 text-white' : 'border border-slate-200 bg-white text-slate-400' }}">{{ $question }}</span>
                                @endforeach
                            </div>
                        </aside>

                        <div class="space-y-3 p-3 sm:p-4">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-2 transition duration-300 hover:-translate-y-0.5"><p class="text-[7px] font-bold text-blue-500">@lang('core::app.ai_card.generated_label')</p><p class="mt-0.5 text-[11px] font-black text-blue-700">19</p></div>
                                <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-2.5 py-2 transition duration-300 hover:-translate-y-0.5"><p class="text-[7px] font-bold text-emerald-600">@lang('core::app.ai_card.reviewed_label')</p><p class="mt-0.5 text-[11px] font-black text-emerald-700">12</p></div>
                                <div class="rounded-lg border border-violet-100 bg-violet-50 px-2.5 py-2 transition duration-300 hover:-translate-y-0.5"><p class="text-[7px] font-bold text-violet-500">@lang('core::app.ai_card.accuracy_label')</p><p class="mt-0.5 text-[11px] font-black text-violet-700">98%</p></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[9px] font-black uppercase tracking-wide text-slate-400">@lang('core::app.ai_card.question_list_label')</p>
                                <span class="rounded-full bg-green-50 px-2 py-1 text-[8px] font-black text-green-700">2 @lang('core::app.question.answered')</span>
                            </div>
                            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition duration-300 group-hover:border-green-200">
                                <div class="flex items-start justify-between gap-3"><div><p class="text-[10px] font-black text-slate-800">@lang('core::app.ai_card.question_1_label')</p><p class="mt-0.5 text-[8px] font-semibold text-slate-400">@lang('core::app.ai_card.video_meta')</p></div><span class="shrink-0 rounded-full bg-green-50 px-2 py-1 text-[8px] font-black text-green-700">@lang('core::app.question.answered')</span></div>
                                <p class="mt-2 text-[8px] font-bold text-slate-600">@lang('core::app.ai_card.question_text')</p>
                                <div class="mt-2 grid grid-cols-2 gap-1.5"><div class="rounded-md border border-slate-200 px-2 py-1.5 text-[7px] text-slate-500">@lang('core::app.ai_card.option_a')</div><div class="ai-desktop-answer flex items-center justify-between rounded-md border px-2 py-1.5 text-[7px] font-bold"><span>@lang('core::app.ai_card.option_b')</span><span class="ai-answer-check">✓</span></div></div>
                            </article>
                            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition duration-300 group-hover:border-green-200">
                                <div class="flex items-start justify-between gap-3"><div><p class="text-[10px] font-black text-slate-800">@lang('core::app.ai_card.question_2_label')</p><p class="mt-0.5 text-[8px] font-semibold text-slate-400">@lang('core::app.ai_card.document_meta')</p></div><span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[8px] font-black text-slate-500">@lang('core::app.ai_card.draft')</span></div>
                                <div class="mt-3 flex items-center justify-between gap-2"><div class="flex min-w-0 items-center gap-2"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-md bg-green-50 text-green-700"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/></svg></span><span class="truncate text-[8px] font-semibold text-slate-500">@lang('core::app.ai_card.source_value')</span></div><span class="shrink-0 text-[7px] font-black text-green-700">@lang('core::app.ai_card.correct_answer')</span></div>
                            </article>
                        </div>
                    </div>

                    {{-- Animated pointer demonstrates reviewing and saving a generated question. --}}
                    <div class="ai-demo-cursor pointer-events-none absolute left-0 top-0 z-40 hidden sm:block" aria-hidden="true">
                        <span class="ai-demo-click absolute -left-2 -top-2 h-7 w-7 rounded-full border-2 border-blue-400"></span>
                        <svg class="relative h-6 w-6 drop-shadow-md" viewBox="0 0 24 24" fill="none"><path d="M5 3.5 18.3 13l-6.1 1.1-3.6 5.1L5 3.5Z" fill="#2563eb" stroke="white" stroke-width="1.5" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="ai-save-toast pointer-events-none absolute bottom-4 left-1/2 z-30 flex -translate-x-1/2 items-center gap-2 rounded-xl border border-green-100 bg-white px-3 py-2 shadow-xl" aria-hidden="true"><span class="grid h-5 w-5 place-items-center rounded-full bg-green-500 text-[9px] font-black text-white">✓</span><span class="whitespace-nowrap text-[8px] font-black text-slate-700">@lang('core::app.ai_card.save_success')</span></div>
                </div>

                {{-- Mobile preview: same device language as the other product sections --}}
                <div class="absolute -bottom-1 right-3 z-20 w-32 sm:-right-1 sm:w-36 lg:-right-3 lg:w-40" style="animation: floatBadge 4.5s ease-in-out infinite">
                    <div class="relative bg-white shadow-2xl transition duration-500 ease-out group-hover:-translate-y-1" style="aspect-ratio: 9 / 18.5; border: 2px solid #34d399; border-radius: 2rem; transform: rotate(4deg);">
                        <div class="flex h-full flex-col overflow-hidden bg-white" style="border-radius: calc(2rem - 2px);">
                            <div class="flex justify-center pb-0 pt-2"><span class="h-3 w-10 rounded-full bg-slate-900"></span></div>
                            <div class="flex items-center justify-between px-3 py-0.5">
                                <span class="text-[7px] font-black text-slate-700">17:30</span>
                                <div class="flex items-center gap-1 text-slate-600" aria-hidden="true">
                                    <svg width="8" height="6" fill="currentColor" viewBox="0 0 10 8"><rect x="0" y="4" width="2" height="4" rx=".5"/><rect x="2.5" y="2.5" width="2" height="5.5" rx=".5"/><rect x="5" y="1" width="2" height="7" rx=".5"/><rect x="7.5" width="2" height="8" rx=".5"/></svg>
                                    <svg width="9" height="6" fill="none" viewBox="0 0 12 8"><rect x=".5" y=".5" width="9" height="7" rx="1.5" stroke="currentColor"/><path d="M11 3v2" stroke="currentColor" stroke-linecap="round"/><rect x="2" y="2" width="6" height="4" rx=".7" fill="currentColor"/></svg>
                                </div>
                            </div>

                            <div class="bg-green-600 px-3 py-2 text-white">
                                <div class="flex items-center justify-between"><p class="text-[7px] font-black">@lang('core::app.ai_card.section_1_name')</p><span class="text-[6px] font-bold text-green-100">2/5</span></div>
                                <div class="mt-1 h-1 overflow-hidden rounded-full bg-green-700"><div class="ai-phone-progress h-full rounded-full bg-white"></div></div>
                            </div>

                            <div class="flex flex-1 flex-col p-2.5">
                                <div class="mb-2 flex items-start justify-between gap-1">
                                    <div><p class="text-[7px] font-black text-slate-800">@lang('core::app.ai_card.question_1_label')</p><p class="text-[6px] font-semibold text-slate-400">@lang('core::app.question.single_choice')</p></div>
                                    <span class="rounded-full bg-green-50 px-1.5 py-0.5 text-[5px] font-black text-green-700">@lang('core::app.question.answered')</span>
                                </div>
                                <p class="mb-2 min-h-4 text-[6px] font-bold leading-tight text-slate-600"><span class="ai-phone-typing inline-block max-w-full overflow-hidden whitespace-nowrap align-bottom">@lang('core::app.ai_card.question_text')</span></p>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 rounded-md border border-slate-200 px-1.5 py-1"><span class="h-2.5 w-2.5 rounded-full border border-slate-300"></span><span class="text-[6px] text-slate-500">@lang('core::app.ai_card.option_a')</span></div>
                                    <div class="ai-phone-answer flex items-center gap-1.5 rounded-md border border-green-300 bg-green-50 px-1.5 py-1"><span class="grid h-2.5 w-2.5 place-items-center rounded-full bg-green-600"><span class="h-1 w-1 rounded-full bg-white"></span></span><span class="text-[6px] font-bold text-green-700">@lang('core::app.ai_card.option_b')</span></div>
                                </div>
                                <div class="mt-2 rounded-md border border-green-100 bg-green-50 p-1.5"><p class="text-[5.5px] font-semibold leading-tight text-green-700">@lang('core::app.mobile.explanation')</p></div>
                                <div class="mt-auto flex gap-1 pt-2"><button type="button" class="flex-1 rounded-md bg-slate-100 py-1.5 text-[6px] font-bold text-slate-500">@lang('core::app.mobile.prev')</button><button type="button" class="flex-1 rounded-md bg-green-500 py-1.5 text-[6px] font-black text-white shadow-[0_1px_0_#15803d]">@lang('core::app.mobile.next')</button></div>
                            </div>
                            <div class="flex justify-center pb-1.5"><span class="h-0.5 w-8 rounded-full bg-slate-300"></span></div>
                        </div>
                    </div>
                </div>

                <span class="pointer-events-none absolute bottom-8 left-3 text-2xl text-violet-400" style="animation: floatStar 4s ease-in-out infinite" aria-hidden="true">&#10022;</span>
                <span class="pointer-events-none absolute left-4 top-14 h-2.5 w-2.5 rounded-full bg-amber-300/80" style="animation: floatStar 3.2s .4s ease-in-out infinite" aria-hidden="true"></span>
            </div>
        </div>
    </div>
</section>

<style>
@keyframes aiDemoCursor {
    0%, 12% { opacity: 0; transform: translate(430px, 82px) scale(.9); }
    18%, 34% { opacity: 1; transform: translate(430px, 82px) scale(1); }
    48%, 62% { opacity: 1; transform: translate(390px, 292px) scale(1); }
    76%, 90% { opacity: 1; transform: translate(515px, 82px) scale(.92); }
    100% { opacity: 0; transform: translate(515px, 82px) scale(.92); }
}
@keyframes aiDemoClick {
    0%, 30%, 45%, 62%, 75%, 92%, 100% { opacity: 0; transform: scale(.45); }
    34%, 66%, 96% { opacity: .75; transform: scale(1); }
}
@keyframes aiPhoneTyping {
    0%, 12% { width: 0; border-right-color: #16a34a; }
    48%, 82% { width: 100%; border-right-color: #16a34a; }
    90%, 100% { width: 100%; border-right-color: transparent; }
}
@keyframes aiPhoneAnswer {
    0%, 50% { opacity: .45; transform: scale(.98); }
    58%, 84% { opacity: 1; transform: scale(1.02); box-shadow: 0 0 0 2px rgba(34,197,94,.12); }
    100% { opacity: 1; transform: scale(1); }
}
@keyframes aiPhoneProgress {
    0%, 15% { width: 12%; }
    55%, 100% { width: 40%; }
}
@keyframes aiDesktopAnswer {
    0%, 44%, 100% { color: #64748b; background: #fff; border-color: #e2e8f0; box-shadow: none; }
    50%, 82% { color: #15803d; background: #f0fdf4; border-color: #86efac; box-shadow: 0 0 0 2px rgba(34,197,94,.1); }
}
@keyframes aiAnswerCheck {
    0%, 44%, 100% { opacity: 0; transform: scale(.4); }
    50%, 82% { opacity: 1; transform: scale(1); }
}
@keyframes aiSaveButton {
    0%, 73%, 100% { background: #22c55e; transform: translateY(0); }
    77%, 92% { background: #16a34a; transform: translateY(1px); box-shadow: 0 1px 0 #15803d; }
}
@keyframes aiSaveLabel {
    0%, 73%, 100% { opacity: 1; transform: translateY(0); }
    77%, 92% { opacity: 0; transform: translateY(-8px); }
}
@keyframes aiSaveCheck {
    0%, 73%, 100% { opacity: 0; transform: translateY(8px) scale(.5); }
    77%, 92% { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes aiSaveToast {
    0%, 76%, 100% { opacity: 0; transform: translate(-50%, 10px) scale(.96); }
    80%, 94% { opacity: 1; transform: translate(-50%, 0) scale(1); }
}
.ai-demo-cursor { animation: aiDemoCursor 8s ease-in-out infinite; }
.ai-demo-click { animation: aiDemoClick 8s ease-out infinite; }
.ai-desktop-answer { animation: aiDesktopAnswer 8s ease-in-out infinite; }
.ai-answer-check { animation: aiAnswerCheck 8s ease-out infinite; }
.ai-save-button { animation: aiSaveButton 8s ease-in-out infinite; }
.ai-save-label { display: inline-block; animation: aiSaveLabel 8s ease-in-out infinite; }
.ai-save-check { animation: aiSaveCheck 8s ease-in-out infinite; }
.ai-save-toast { opacity: 0; animation: aiSaveToast 8s ease-in-out infinite; }
.ai-phone-typing { border-right: 1px solid #16a34a; animation: aiPhoneTyping 7s steps(28, end) infinite; }
.ai-phone-answer { animation: aiPhoneAnswer 7s ease-in-out infinite; }
.ai-phone-progress { animation: aiPhoneProgress 7s ease-in-out infinite; }
@media (prefers-reduced-motion: reduce) {
    .ai-demo-cursor, .ai-demo-click, .ai-desktop-answer, .ai-answer-check, .ai-save-button, .ai-save-label, .ai-save-check, .ai-save-toast, .ai-phone-typing, .ai-phone-answer, .ai-phone-progress { animation: none !important; }
    .ai-demo-cursor { display: none !important; }
    .ai-desktop-answer { color: #15803d; background: #f0fdf4; border-color: #86efac; }
    .ai-answer-check { opacity: 1; transform: scale(1); }
    .ai-save-check, .ai-save-toast { display: none; }
    .ai-phone-typing { width: 100%; border-right-color: transparent; }
    .ai-phone-progress { width: 40%; }
}
</style>
