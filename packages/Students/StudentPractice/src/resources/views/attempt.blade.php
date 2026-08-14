@extends('Mindigo-dashboard::layouts')

@section('title', __('student-practice::app.title').' - Auronsoft LMS')
@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css', 'packages/Mindigo/Dashboard/src/resources/js/app.js'])
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-4">
        <div><p class="text-[11px] font-black uppercase tracking-widest text-green-700">@lang('student-practice::app.area')</p><h1 class="text-lg font-black text-slate-950">{{ $attempt->practiceSet?->title ?? __('student-practice::app.title') }}</h1></div>
        <form action="{{ route('student.practice.complete', $attempt) }}" method="POST">@csrf<button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-black text-white hover:bg-green-700">@lang('student-practice::app.finish')</button></form>
    </header>
    <main class="mx-auto max-w-5xl space-y-5 p-6">
        @foreach($attempt->answers as $index => $practiceAnswer)
            <article class="rounded-xl border border-slate-200 bg-white p-6">
                <p class="text-xs font-black uppercase tracking-wide text-green-700">{{ __('student-practice::app.question', ['current' => $index + 1, 'total' => $attempt->answers->count()]) }}</p>
                <div class="mt-3 text-sm font-semibold leading-7 text-slate-900">{!! $practiceAnswer->display_content !!}</div>
                <form class="mt-5 space-y-3" data-practice-answer action="{{ route('student.practice.submit-answer', $attempt) }}" method="POST">
                    @csrf<input type="hidden" name="question_id" value="{{ $practiceAnswer->question_id }}">
                    @if($practiceAnswer->display_type === 'single_choice')
                        @foreach($practiceAnswer->display_options as $option)
                            @php($value = data_get($option, 'id', data_get($option, 'key')))
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 hover:border-green-300"><input type="radio" name="answer[choice]" value="{{ $value }}" @checked((string) data_get($practiceAnswer->student_answer, 'choice') === (string) $value)><span class="text-sm font-semibold text-slate-700">{{ data_get($option, 'content', data_get($option, 'text')) }}</span></label>
                        @endforeach
                        <p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.single_choice_hint')</p>
                    @elseif($practiceAnswer->display_type === 'multiple_choice')
                        @foreach($practiceAnswer->display_options as $option)
                            @php($value = data_get($option, 'id', data_get($option, 'key')))
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 hover:border-green-300"><input type="checkbox" name="answer[choices][]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', data_get($practiceAnswer->student_answer, 'choices', [])), true))><span class="text-sm font-semibold text-slate-700">{{ data_get($option, 'content', data_get($option, 'text')) }}</span></label>
                        @endforeach
                        <p class="text-xs font-semibold text-slate-400">@lang('student-practice::app.multiple_choice_hint')</p>
                    @elseif($practiceAnswer->display_type === 'true_false')
                        @foreach([1 => __('student-practice::app.correct'), 0 => __('student-practice::app.incorrect')] as $value => $label)<label class="mr-4 inline-flex items-center gap-2 text-sm font-semibold"><input type="radio" name="answer[answer]" value="{{ $value }}" @checked((string) data_get($practiceAnswer->student_answer, 'answer') === (string) $value)>{{ $label }}</label>@endforeach
                    @else
                        <textarea name="answer[text]" rows="4" aria-label="@lang('student-practice::app.your_answer')" class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold" placeholder="@lang('student-practice::app.answer_placeholder')">{{ data_get($practiceAnswer->student_answer, 'text') }}</textarea>
                    @endif
                    <div class="flex items-center gap-3"><button type="submit" class="rounded-lg border border-green-600 px-4 py-2 text-xs font-black text-green-700 hover:bg-green-50">@lang('student-practice::app.save_answer')</button><span class="text-xs font-bold text-green-700" role="status" aria-live="polite" data-practice-status></span></div>
                </form>
            </article>
        @endforeach
    </main>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-practice-answer]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const status = form.querySelector('[data-practice-status]');
        const button = form.querySelector('button[type="submit"], button:not([type])');
        if (button?.disabled) return;
        if (button) button.disabled = true;
        status.textContent = @json(__('student-practice::app.messages.answer_saving'));
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message);
            status.textContent = payload.message;
        } catch (error) {
            status.textContent = @json(__('student-practice::app.messages.answer_save_failed'));
        } finally {
            if (button) button.disabled = false;
        }
    });
});
</script>
@endpush
