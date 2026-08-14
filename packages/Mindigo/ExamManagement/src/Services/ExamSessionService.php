<?php

namespace Mindigo\ExamManagement\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamCandidate;
use Mindigo\ExamManagement\Models\ExamSession;
use Mindigo\ExamManagement\Models\ExamTemplate;
use Mindigo\ExamManagement\Models\ExamTemplateVersion;
use Mindigo\TeacherClassroom\Models\Classroom;

class ExamSessionService
{
    public function listFor(User $teacher): Builder
    {
        return ExamSession::query()->whereBelongsTo($teacher, 'organizer')
            ->with('version.template')->withCount('candidates')->latest('starts_at');
    }

    public function readyVersions(User $teacher): Collection
    {
        return ExamTemplateVersion::query()
            ->whereNotNull('locked_at')
            ->whereHas('template', fn (Builder $query) => $query->where('owner_id', $teacher->getAuthIdentifier())->where('status', ExamTemplate::STATUS_READY))
            ->with('template')->latest('version')->get();
    }

    public function classrooms(User $teacher): Collection
    {
        return Classroom::query()->where('teacher_id', $teacher->getAuthIdentifier())
            ->where('status', 'active')->withCount(['students' => fn (Builder $query) => $query->where('classroom_students.status', 'active')])
            ->orderBy('name')->get();
    }

    public function create(User $teacher, array $data): ExamSession
    {
        return DB::transaction(function () use ($teacher, $data): ExamSession {
            $version = ExamTemplateVersion::query()->with('template')->findOrFail($data['exam_template_version_id']);
            if ((int) $version->template->owner_id !== (int) $teacher->getAuthIdentifier() || ! $version->isLocked()) {
                throw ValidationException::withMessages(['exam_template_version_id' => __('Mindigo-exam-management::app.session_builder.invalid_template')]);
            }

            $classrooms = Classroom::query()->whereIn('id', $data['classroom_ids'])
                ->where('teacher_id', $teacher->getAuthIdentifier())->where('status', 'active')->with(['students' => fn ($query) => $query->where('classroom_students.status', 'active')])->get();
            if ($classrooms->count() !== count(array_unique($data['classroom_ids']))) {
                throw ValidationException::withMessages(['classroom_ids' => __('Mindigo-exam-management::app.session_builder.invalid_classroom')]);
            }

            $session = ExamSession::query()->create([
                'exam_template_version_id' => $version->id,
                'organizer_id' => $teacher->getAuthIdentifier(),
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'status' => ExamSession::STATUS_SCHEDULED,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'duration_minutes' => $data['duration_minutes'],
                'max_attempts' => $data['max_attempts'],
                'passing_score' => $data['passing_score'],
                'result_policy' => $data['result_policy'],
                'shuffle_questions' => $data['shuffle_questions'] ?? true,
                'shuffle_answers' => $data['shuffle_answers'] ?? true,
                'anonymous_grading' => $data['anonymous_grading'] ?? false,
                'security_policy' => $data['security_policy'] ?? [],
                'scheduled_at' => now(),
            ]);

            foreach ($classrooms as $classroom) {
                $session->assignments()->create(['assignable_type' => Classroom::class, 'assignable_id' => $classroom->id, 'assigned_by' => $teacher->getAuthIdentifier(), 'assigned_at' => now()]);
            }

            $students = $classrooms->pluck('students')->flatten()->unique('id');
            $session->candidates()->createMany($students->map(fn (User $student) => [
                'user_id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'status' => ExamCandidate::STATUS_ELIGIBLE,
                'metadata' => ['classroom_ids' => $classrooms->filter(fn (Classroom $classroom) => $classroom->students->contains($student))->pluck('id')->values()->all()],
            ])->all());

            return $session->fresh(['assignments.assignable', 'candidates', 'version.template']);
        });
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'exam-session';
        $slug = $base;
        $suffix = 2;
        while (ExamSession::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
