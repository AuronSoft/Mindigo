<?php

namespace Mindigo\LearningTools\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MindigoBotService
{
    public function reply(string $message, array $history, string $subject, string $mode): array
    {
        $keys = collect(config('services.mindigobot.keys', []))->filter()->unique()->values();
        if ($keys->isEmpty() && config('services.mindigobot.key')) {
            $keys->push(config('services.mindigobot.key'));
        }
        if ($keys->isEmpty()) {
            throw new RuntimeException(__('learning-tools::app.ai.errors.not_configured'));
        }
        try {
            $model = config('services.mindigobot.model');
            $contents = collect(array_slice($history, -10))->map(fn (array $item) => ['role' => $item['role'] === 'assistant' ? 'model' : 'user', 'parts' => [['text' => $item['content']]]])->all();
            $response = null;
            foreach ($keys as $key) {
                try {
                    $response = Http::withHeaders(['x-goog-api-key' => $key])->timeout(60)->post(config('services.mindigobot.url').'/models/'.$model.':generateContent', [
                        'systemInstruction' => ['parts' => [['text' => $this->instructions($subject, $mode)]]],
                        'contents' => $contents,
                        'generationConfig' => ['maxOutputTokens' => 1200, 'temperature' => 0.4],
                        'safetySettings' => collect(['HARM_CATEGORY_HARASSMENT', 'HARM_CATEGORY_HATE_SPEECH', 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'HARM_CATEGORY_DANGEROUS_CONTENT'])->map(fn ($category) => ['category' => $category, 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'])->all(),
                    ])->throw()->json();
                    break;
                } catch (RequestException $exception) {
                    if (! in_array($exception->response->status(), [403, 429], true)) {
                        throw $exception;
                    }
                }
            }
            if ($response === null) {
                throw new RuntimeException(__('learning-tools::app.ai.errors.unavailable'));
            }
        } catch (ConnectionException $exception) {
            throw new RuntimeException(__('learning-tools::app.ai.errors.unavailable'), previous: $exception);
        }
        if (data_get($response, 'promptFeedback.blockReason')) {
            throw new RuntimeException(__('learning-tools::app.ai.errors.unsafe'));
        }
        $text = collect(data_get($response, 'candidates.0.content.parts', []))->pluck('text')->join("\n");
        if (! $text) {
            throw new RuntimeException(__('learning-tools::app.ai.errors.empty'));
        }

        return ['content' => $text, 'response_id' => null, 'input_tokens' => data_get($response, 'usageMetadata.promptTokenCount'), 'output_tokens' => data_get($response, 'usageMetadata.candidatesTokenCount')];
    }

    private function instructions(string $subject, string $mode): string
    {
        return "You are Mindigo's education tutor. Subject: {$subject}. Learning mode: {$mode}. Teach step by step, ask guiding questions, adapt to the learner's level, and use clear Vietnamese unless the learner uses another language. Do not complete active exams or facilitate cheating. Do not request personal or sensitive data. State uncertainty and encourage checking teachers or official sources. For harmful or unrelated requests, refuse briefly and redirect to learning.";
    }
}
