<?php

namespace Mindigo\SubjectManagement\Services;

use Illuminate\Support\Str;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\Exam;
use Mindigo\QuestionBank\Models\Question;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\SubjectManagement\Models\SubjectTopic;

class SubjectManagementService
{
    public function filteredList(array $filters)
    {
        $query = Subject::query()
            ->withCount('topics')
            ->orderBy('sort_order')
            ->orderBy('name');

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(12)->withQueryString();
    }

    public function create(User $user, array $data): Subject
    {
        $subject = Subject::query()->create([
            ...$data,
            'created_by' => $user->getAuthIdentifier(),
            'code' => Str::upper($data['code']),
            'slug' => $this->uniqueSubjectSlug($data['name']),
        ]);

        $this->audit('create', [], $subject->only($this->auditFields()), $subject);

        return $subject;
    }

    public function update(Subject $subject, array $data): Subject
    {
        $oldValues = $subject->only($this->auditFields());
        $subject->fill([
            ...$data,
            'code' => Str::upper($data['code']),
            'slug' => $subject->name === $data['name'] ? $subject->slug : $this->uniqueSubjectSlug($data['name'], $subject),
        ])->save();

        $this->audit('update', $oldValues, $subject->only($this->auditFields()), $subject);

        return $subject;
    }

    public function delete(Subject $subject): void
    {
        $oldValues = $subject->only($this->auditFields());
        $subject->delete();
        $this->audit('delete', $oldValues, [], $subject);
    }

    public function createTopic(Subject $subject, array $data): SubjectTopic
    {
        $topic = $subject->topics()->create([
            ...$data,
            'slug' => $this->uniqueTopicSlug($subject, $data['name']),
        ]);

        $this->auditTopic('create', [], $topic->only($this->topicAuditFields()), $topic);

        return $topic;
    }

    public function updateTopic(SubjectTopic $topic, array $data): SubjectTopic
    {
        $oldValues = $topic->only($this->topicAuditFields());
        $topic->fill([
            ...$data,
            'slug' => $topic->name === $data['name'] ? $topic->slug : $this->uniqueTopicSlug($topic->subject, $data['name'], $topic),
        ])->save();

        $this->auditTopic('update', $oldValues, $topic->only($this->topicAuditFields()), $topic);

        return $topic;
    }

    public function deleteTopic(SubjectTopic $topic): void
    {
        $oldValues = $topic->only($this->topicAuditFields());
        $topic->delete();
        $this->auditTopic('delete', $oldValues, [], $topic);
    }

    public function stats(): array
    {
        return [
            'total' => Subject::query()->count(),
            'active' => Subject::query()->where('status', 'active')->count(),
            'topics' => SubjectTopic::query()->count(),
            'inactive' => Subject::query()->where('status', 'inactive')->count(),
        ];
    }

    public function usageFor(Subject $subject): array
    {
        return [
            'questions' => class_exists(Question::class) ? Question::query()->where('subject', $subject->name)->count() : 0,
            'exams' => class_exists(Exam::class) ? Exam::query()->where('subject', $subject->name)->count() : 0,
        ];
    }

    public function activeSubjectNames(): array
    {
        return Subject::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function can(User $user, string $permission): bool
    {
        return method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission);
    }

    private function uniqueSubjectSlug(string $name, ?Subject $ignore = null): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (Subject::query()->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function uniqueTopicSlug(Subject $subject, string $name, ?SubjectTopic $ignore = null): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while ($subject->topics()->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function auditFields(): array
    {
        return ['id', 'name', 'code', 'slug', 'color', 'status', 'sort_order'];
    }

    private function topicAuditFields(): array
    {
        return ['id', 'subject_id', 'name', 'slug', 'status', 'sort_order'];
    }

    private function audit(string $action, array $oldValues, array $newValues, Subject $subject): void
    {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->record(
            $action,
            'subjects',
            $oldValues,
            $newValues,
            ['subject_id' => $subject->id, 'name' => $subject->name],
            $subject
        );
    }

    private function auditTopic(string $action, array $oldValues, array $newValues, SubjectTopic $topic): void
    {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->record(
            $action,
            'subject_topics',
            $oldValues,
            $newValues,
            ['topic_id' => $topic->id, 'subject_id' => $topic->subject_id, 'name' => $topic->name],
            $topic
        );
    }
}
