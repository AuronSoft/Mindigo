@props(['question', 'index', 'saved'])

<div id="question-{{ $index }}" class="question-item {{ $index !== 0 ? 'hidden' : '' }} bg-white rounded-3xl border border-slate-200 p-8 mb-8">

    <div class="flex items-center justify-between mb-6">
        <span class="px-4 py-1.5 bg-slate-100 text-slate-600 font-bold rounded-2xl text-sm">
            Câu {{ $index + 1 }}
        </span>
        <span class="text-sm font-semibold text-slate-400">{{ $question->points ?? 1 }} @lang('student-exam::app.points')</span>
    </div>

    <h3 class="text-lg font-bold text-slate-800 leading-relaxed mb-6">
        {!! $question->content !!}
    </h3>

    @if($question->type === 'multiple_choice' || $question->type === 'single_choice')
        <div class="space-y-3">
            @foreach($question->options as $option)
                <label class="flex items-center gap-3 border border-slate-200 hover:border-blue-200 rounded-2xl p-4 cursor-pointer transition">
                    <input 
                        type="{{ $question->type === 'single_choice' ? 'radio' : 'checkbox' }}"
                        name="answers[{{ $question->id }}]{{ $question->type === 'multiple_choice' ? '[]' : '' }}"
                        value="{{ $option->id }}"
                        @if($saved && 
                            ($question->type === 'single_choice' && $saved->answer_value == $option->id) ||
                            ($question->type === 'multiple_choice' && in_array($option->id, json_decode($saved->answer_value ?? '[]', true) ?? [])))
                            checked
                        @endif
                        class="w-5 h-5 accent-blue-600">
                    <span class="font-medium text-slate-700">{{ $option->content }}</span>
                </label>
            @endforeach
        </div>
    @elseif($question->type === 'essay')
        <textarea name="answers[{{ $question->id }}]" rows="8"
                  class="w-full border border-slate-200 rounded-3xl p-6 focus:outline-none focus:border-blue-300 font-medium"
        >{{ $saved?->answer_value ?? '' }}</textarea>
    @endif
</div>