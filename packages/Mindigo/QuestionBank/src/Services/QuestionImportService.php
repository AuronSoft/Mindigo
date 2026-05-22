<?php

namespace Mindigo\QuestionBank\Services;

use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\QuestionBank\Models\QuestionFolder;

class QuestionImportService
{
    public function rowsFromFile($file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return $extension === 'json'
            ? $this->parseJson((string) file_get_contents($file->getRealPath()))
            : $this->parseCsv($file->getRealPath());
    }

    public function questionDataFromRow(array $row, User $user, string $defaultStatus, ?int $defaultFolderId, int $rowNumber): array
    {
        $type = trim((string) ($row['type'] ?? 'single_choice'));
        $difficulty = trim((string) ($row['difficulty'] ?? 'medium'));
        $subject = trim((string) ($row['subject'] ?? ''));
        $content = trim((string) ($row['content'] ?? $row['question'] ?? ''));

        if ($subject === '' || $content === '') {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_missing_required', ['row' => $rowNumber]),
            ]);
        }

        if (!in_array($type, Question::TYPES, true)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_type', ['row' => $rowNumber]),
            ]);
        }

        if (!in_array($difficulty, Question::DIFFICULTIES, true)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_difficulty', ['row' => $rowNumber]),
            ]);
        }

        $status = trim((string) ($row['status'] ?? '')) ?: $defaultStatus;
        if (!in_array($status, ['draft', 'reviewing'], true)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_status', ['row' => $rowNumber]),
            ]);
        }

        $options = $this->splitValue($row['options'] ?? []);
        $correctAnswers = $this->splitValue($row['correct_answers'] ?? $row['answer'] ?? []);

        if (!in_array($type, ['short_answer', 'essay'], true) && count($options) < 2) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_missing_options', ['row' => $rowNumber]),
            ]);
        }

        if ($type !== 'essay' && empty($correctAnswers)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_missing_answer', ['row' => $rowNumber]),
            ]);
        }

        return [
            'created_by' => $user->getAuthIdentifier(),
            'folder_id' => $this->folderIdFromRow($row, $user, $defaultFolderId),
            'subject' => $subject,
            'topic' => trim((string) ($row['topic'] ?? '')) ?: null,
            'type' => $type,
            'difficulty' => $difficulty,
            'status' => $status,
            'content' => $content,
            'options' => in_array($type, ['short_answer', 'essay'], true) ? [] : $options,
            'correct_answers' => $correctAnswers,
            'explanation' => trim((string) ($row['explanation'] ?? '')) ?: null,
            'tags' => $this->splitValue($row['tags'] ?? []),
        ];
    }

    private function parseJson(string $contents): array
    {
        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_invalid_json'),
            ]);
        }

        return array_is_list($decoded) ? $decoded : ($decoded['questions'] ?? []);
    }

    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if (!$handle) {
            throw ValidationException::withMessages([
                'import_file' => __('Mindigo-question-bank::app.validation.import_unreadable'),
            ]);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);

            return [];
        }

        $headers = array_map(fn ($header) => trim(strtolower((string) $header)), $headers);
        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($headers, array_pad($values, count($headers), '')) ?: [];
        }

        fclose($handle);

        return $rows;
    }

    private function folderIdFromRow(array $row, User $user, ?int $defaultFolderId): ?int
    {
        $folderName = trim((string) ($row['folder'] ?? ''));

        if ($folderName === '') {
            return $defaultFolderId;
        }

        $query = QuestionFolder::query()->where('name', $folderName);
        if (!$user->isAdmin()) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        return $query->value('id') ?: QuestionFolder::query()->create([
            'created_by' => $user->getAuthIdentifier(),
            'name' => $folderName,
            'subject' => trim((string) ($row['subject'] ?? '')) ?: null,
            'color' => 'green',
        ])->id;
    }

    private function splitValue(array|string|null $value): array
    {
        if (is_array($value)) {
            return $this->cleanArray($value);
        }

        return $this->cleanArray(preg_split('/\||\R/', (string) $value) ?: []);
    }

    private function cleanArray(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
    }
}
