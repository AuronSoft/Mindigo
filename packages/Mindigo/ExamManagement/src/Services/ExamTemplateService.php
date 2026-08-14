<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Mindigo\QuestionBank\Models\Question;

class ExamTemplateService
{
    public function listFor(User $teacher): Builder
    {
        return ExamTemplate::query()
            ->whereBelongsTo($teacher, 'owner')
            ->withCount('versions')
            ->latest('updated_at');
    }

    public function availableQuestions(User $teacher): Collection
    {
        return Question::query()
            ->where(function (Builder $query) use ($teacher): void {
                $query->where('created_by', $teacher->getAuthIdentifier())
                    ->whereNot('status', 'rejected')
                    ->orWhere('status', 'approved');
            })
            ->latest()
            ->get();
    }

    public function create(User $teacher, array $data): ExamTemplate
    {
        return DB::transaction(function () use ($teacher, $data): ExamTemplate {
            $template = ExamTemplate::query()->create([
                'owner_id' => $teacher->getAuthIdentifier(),
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'subject' => $data['subject'] ?? null,
                'topic' => $data['topic'] ?? null,
                'description' => $data['description'] ?? null,
                'instructions' => $data['instructions'] ?? null,
            ]);

            $this->writeVersion($template, $teacher, $data, 1);

            return $template->fresh('versions.sections.questions');
        });
    }

    public function update(ExamTemplate $template, User $teacher, array $data): ExamTemplate
    {
        return DB::transaction(function () use ($template, $teacher, $data): ExamTemplate {
            $current = $template->versions()->where('version', $template->current_version)->first();
            $versionNumber = $current?->isLocked() ? $template->current_version + 1 : $template->current_version;

            if ($current && ! $current->isLocked()) {
                $current->delete();
            }

            $template->update([
                'title' => $data['title'],
                'subject' => $data['subject'] ?? null,
                'topic' => $data['topic'] ?? null,
                'description' => $data['description'] ?? null,
                'instructions' => $data['instructions'] ?? null,
                'status' => ExamTemplate::STATUS_DRAFT,
                'current_version' => $versionNumber,
                'ready_at' => null,
            ]);
            $this->writeVersion($template, $teacher, $data, $versionNumber);

            return $template->fresh('versions.sections.questions');
        });
    }

    public function markReady(ExamTemplate $template): ExamTemplate
    {
        return DB::transaction(function () use ($template): ExamTemplate {
            $version = $template->versions()->where('version', $template->current_version)->firstOrFail();
            if ($version->questions()->doesntExist()) {
                throw ValidationException::withMessages(['template' => __('Mindigo-exam-management::app.template_builder.requires_question')]);
            }

            $version->update(['locked_at' => now()]);
            $template->update(['status' => ExamTemplate::STATUS_READY, 'ready_at' => now()]);

            return $template->fresh();
        });
    }

    private function writeVersion(ExamTemplate $template, User $teacher, array $data, int $number): void
    {
        $questionIds = collect($data['sections'])->pluck('questions')->flatten(1)->pluck('id')->unique();
        $questions = Question::query()
            ->whereIn('id', $questionIds)
            ->where(function (Builder $query) use ($teacher): void {
                $query->where('created_by', $teacher->getAuthIdentifier())
                    ->whereNot('status', 'rejected')
                    ->orWhere('status', 'approved');
            })->get()->keyBy('id');

        if ($questions->count() !== $questionIds->count()) {
            throw ValidationException::withMessages(['sections' => __('Mindigo-exam-management::app.template_builder.question_not_available')]);
        }

        $version = ExamTemplateVersion::query()->create([
            'exam_template_id' => $template->id,
            'created_by' => $teacher->getAuthIdentifier(),
            'version' => $number,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'settings' => $data['settings'] ?? [],
        ]);

        $questionOrder = 0;
        $totalPoints = 0;
        foreach ($data['sections'] as $sectionOrder => $sectionData) {
            $section = $version->sections()->create([
                'title' => $sectionData['title'],
                'instructions' => $sectionData['instructions'] ?? null,
                'sort_order' => $sectionOrder + 1,
                'shuffle_questions' => $sectionData['shuffle_questions'] ?? false,
            ]);
            foreach ($sectionData['questions'] as $item) {
                $source = $questions->get($item['id']);
                $points = (float) $item['points'];
                $rubric = filled($item['rubric_json'] ?? null) ? json_decode($item['rubric_json'], true, 512, JSON_THROW_ON_ERROR) : null;
                if ($rubric !== null && (! is_array($rubric) || collect($rubric)->contains(fn ($criterion) => ! is_array($criterion) || ! filled($criterion['label'] ?? null) || ! is_numeric($criterion['max_points'] ?? null) || (float) $criterion['max_points'] < 0))) {
                    throw ValidationException::withMessages(['sections' => __('Mindigo-exam-management::app.template_builder.invalid_rubric')]);
                }
                if ($rubric && collect($rubric)->sum(fn ($criterion) => (float) ($criterion['max_points'] ?? 0)) > $points) {
                    throw ValidationException::withMessages(['sections' => __('Mindigo-exam-management::app.template_builder.rubric_exceeds_points')]);
                }
                $version->questions()->create([
                    'exam_section_id' => $section->id,
                    'source_question_id' => $source->id,
                    'sort_order' => ++$questionOrder,
                    'type' => $source->type,
                    'difficulty' => $source->difficulty,
                    'content' => $source->content,
                    'options' => $source->options,
                    'correct_answers' => $source->correct_answers,
                    'explanation' => $source->explanation,
                    'rubric' => $rubric,
                    'points' => $points,
                ]);
                $totalPoints += $points;
            }
        }

        $version->update(['total_questions' => $questionOrder, 'total_points' => $totalPoints]);
        $template->update(['total_questions' => $questionOrder, 'total_points' => $totalPoints]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'exam-template';
        $slug = $base;
        $suffix = 2;
        while (ExamTemplate::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
