<?php

namespace Mindigo\ClassroomManagement\Services;

use Illuminate\Support\Str;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\Auth\Models\User;
use Mindigo\ClassroomManagement\Models\Classroom;
use Mindigo\SubjectManagement\Models\Subject;

class ClassroomManagementService
{
    public function filteredList(User $user, array $filters)
    {
        $query = Classroom::query()
            ->with(['teacher:id,name,email', 'subjects:id,name,color'])
            ->withCount('students')
            ->latest('updated_at');

        $this->scopeForUser($query, $user);

        if (filled($filters['keyword'] ?? null)) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('school_year', 'like', "%{$keyword}%");
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['teacher_id'] ?? null)) {
            $query->where('teacher_id', (int) $filters['teacher_id']);
        }

        if (filled($filters['subject_id'] ?? null)) {
            $query->whereHas('subjects', fn ($subjectQuery) => $subjectQuery->whereKey((int) $filters['subject_id']));
        }

        return $query->paginate(12)->withQueryString();
    }

    public function create(User $user, array $data): Classroom
    {
        $classroom = Classroom::query()->create([
            ...$data,
            'created_by' => $user->getAuthIdentifier(),
            'code' => Str::upper($data['code']),
            'slug' => $this->uniqueSlug($data['name']),
        ]);

        $this->audit('create', [], $classroom->only($this->auditFields()), $classroom);

        return $classroom;
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        $oldValues = $classroom->only($this->auditFields());
        $classroom->fill([
            ...$data,
            'code' => Str::upper($data['code']),
            'slug' => $classroom->name === $data['name'] ? $classroom->slug : $this->uniqueSlug($data['name'], $classroom),
        ])->save();

        $this->audit('update', $oldValues, $classroom->only($this->auditFields()), $classroom);

        return $classroom;
    }

    public function delete(Classroom $classroom): void
    {
        $oldValues = $classroom->only($this->auditFields());
        $classroom->delete();
        $this->audit('delete', $oldValues, [], $classroom);
    }

    public function syncStudents(Classroom $classroom, array $studentIds): void
    {
        $payload = collect($studentIds)
            ->mapWithKeys(fn (int $id) => [$id => ['status' => 'active', 'joined_at' => now()]])
            ->all();

        $classroom->students()->sync($payload);
        $this->audit('sync_students', [], ['student_ids' => $studentIds], $classroom);
    }

    public function syncSubjects(Classroom $classroom, array $subjectIds): void
    {
        $classroom->subjects()->sync($subjectIds);
        $this->audit('sync_subjects', [], ['subject_ids' => $subjectIds], $classroom);
    }

    public function formData(User $user): array
    {
        return [
            'statuses' => Classroom::STATUSES,
            'teachers' => User::query()->teachers()->active()->orderBy('name')->get(['id', 'name', 'email']),
            'students' => User::query()->students()->active()->orderBy('name')->get(['id', 'name', 'email']),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'color']),
        ];
    }

    public function stats(User $user): array
    {
        $query = Classroom::query();
        $this->scopeForUser($query, $user);

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'students' => (clone $query)->withCount('students')->get()->sum('students_count'),
            'inactive' => (clone $query)->where('status', 'inactive')->count(),
        ];
    }

    public function can(User $user, string $permission): bool
    {
        return method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission);
    }

    public function canAccess(User $user, Classroom $classroom): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher()) {
            return (int) $classroom->teacher_id === (int) $user->getAuthIdentifier();
        }

        if ($user->isStudent()) {
            return $classroom->students()->whereKey($user->getAuthIdentifier())->exists();
        }

        return false;
    }

    private function scopeForUser($query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->getAuthIdentifier());

            return;
        }

        if ($user->isStudent()) {
            $query->whereHas('students', fn ($studentQuery) => $studentQuery->whereKey($user->getAuthIdentifier()));
        }
    }

    private function uniqueSlug(string $name, ?Classroom $ignore = null): string
    {
        $base = Str::slug($name) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (Classroom::query()->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function auditFields(): array
    {
        return ['id', 'teacher_id', 'name', 'code', 'slug', 'school_year', 'status'];
    }

    private function audit(string $action, array $oldValues, array $newValues, Classroom $classroom): void
    {
        if (! class_exists(AuditLogService::class)) {
            return;
        }

        app(AuditLogService::class)->record(
            $action,
            'classrooms',
            $oldValues,
            $newValues,
            ['classroom_id' => $classroom->id, 'name' => $classroom->name],
            $classroom
        );
    }
}
