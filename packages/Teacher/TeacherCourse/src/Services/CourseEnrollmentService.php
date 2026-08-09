<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Mindigo\Auth\Models\User;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\Notification\Notifications\CourseAssigned;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseClassroomAssignment;
use Mindigo\TeacherCourse\Models\CourseEnrollment;

class CourseEnrollmentService
{
    public function selfEnroll(User $student, string $slug): CourseEnrollment
    {
        $course = Course::query()->publiclyListed()->where('slug', $slug)->firstOrFail();

        return DB::transaction(function () use ($course, $student): CourseEnrollment {
            $enrollment = CourseEnrollment::query()
                ->where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if ($enrollment && $enrollment->status !== CourseEnrollment::STATUS_WITHDRAWN) {
                return $enrollment;
            }

            $values = [
                'status' => CourseEnrollment::STATUS_ENROLLED,
                'source' => 'self',
                'enrolled_at' => now(),
                'withdrawn_at' => null,
                'last_activity_at' => now(),
            ];

            if ($enrollment) {
                $enrollment->update($values);
            } else {
                CourseEnrollment::query()->insertOrIgnore([[
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    ...$values,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]]);
                $enrollment = CourseEnrollment::query()
                    ->where('course_id', $course->id)
                    ->where('student_id', $student->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $this->syncEnrollmentCount($course);

            return $enrollment;
        });
    }

    public function assignToClassrooms(Course $course, User $teacher, array $data): int
    {
        if (! $course->isPublished()) {
            throw ValidationException::withMessages(['course' => __('teacher-course::learning.published_required')]);
        }

        $classroomIds = $data['classroom_ids'];
        $classrooms = Classroom::query()
            ->whereIn('id', $classroomIds)
            ->where('status', 'active')
            ->when(! $teacher->isAdmin(), fn ($query) => $query->where('teacher_id', $teacher->id))
            ->with(['students' => fn ($query) => $query->where('classroom_students.status', 'active')->where('users.role', 'student')])
            ->get();

        if ($classrooms->count() !== count(array_unique($classroomIds))) {
            throw ValidationException::withMessages(['classroom_ids' => __('teacher-course::learning.invalid_classroom')]);
        }

        $newStudentIds = DB::transaction(function () use ($course, $teacher, $classrooms, $data): Collection {
            $created = collect();

            foreach ($classrooms as $classroom) {
                $distribution = CourseClassroomAssignment::query()->updateOrCreate(
                    ['course_id' => $course->id, 'classroom_id' => $classroom->id],
                    [
                        'assigned_by' => $teacher->id,
                        'assigned_at' => now(),
                        'starts_at' => $data['starts_at'] ?? null,
                        'due_at' => $data['due_at'] ?? null,
                        'is_mandatory' => $data['is_mandatory'],
                        'visibility' => $data['visibility'],
                    ],
                );

                foreach ($classroom->students as $student) {
                    $enrollment = CourseEnrollment::query()
                        ->where('course_id', $course->id)
                        ->where('student_id', $student->id)
                        ->lockForUpdate()
                        ->first();

                    if ($enrollment && $enrollment->status !== CourseEnrollment::STATUS_WITHDRAWN) {
                        if (! $enrollment->distribution_id) {
                            $enrollment->update([
                                'classroom_id' => $classroom->id,
                                'distribution_id' => $distribution->id,
                                'assigned_by' => $teacher->id,
                            ]);
                            $created->push($student->id);
                        }

                        continue;
                    }

                    $values = [
                        'classroom_id' => $classroom->id,
                        'distribution_id' => $distribution->id,
                        'assigned_by' => $teacher->id,
                        'status' => CourseEnrollment::STATUS_INVITED,
                        'source' => 'classroom',
                        'invited_at' => now(),
                        'withdrawn_at' => null,
                        'last_activity_at' => now(),
                    ];

                    if ($enrollment) {
                        $enrollment->update($values);
                        $wasCreated = true;
                    } else {
                        $wasCreated = CourseEnrollment::query()->insertOrIgnore([[
                            'course_id' => $course->id,
                            'student_id' => $student->id,
                            ...$values,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]]) === 1;
                    }

                    if ($wasCreated) {
                        $created->push($student->id);
                    }
                }
            }

            $this->syncEnrollmentCount($course);

            return $created->unique()->values();
        });

        if ($newStudentIds->isNotEmpty()) {
            $students = User::query()->whereIn('id', $newStudentIds)->get();
            Notification::send($students, new CourseAssigned(
                $course->id,
                $course->name,
                $teacher->name,
                route('student.courses.show', $course->slug),
            ));
        }

        return $newStudentIds->count();
    }

    public function teacherClassrooms(User $teacher): Collection
    {
        return Classroom::query()
            ->when(! $teacher->isAdmin(), fn ($query) => $query->where('teacher_id', $teacher->id))
            ->where('status', 'active')
            ->withCount(['students' => fn ($query) => $query->where('classroom_students.status', 'active')])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function studentCourses(User $student): LengthAwarePaginator
    {
        return CourseEnrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', CourseEnrollment::ACTIVE_STATUSES)
            ->availableToStudent()
            ->whereHas('course', fn ($query) => $query
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->whereIn('publication_status', [Course::PUBLICATION_PUBLISHED, Course::PUBLICATION_UNLISTED]))
            ->with(['course.teacher:id,name', 'course.subject:id,name', 'lastLesson:id,name', 'distribution'])
            ->latest('last_activity_at')
            ->latest('id')
            ->paginate(12);
    }

    private function syncEnrollmentCount(Course $course): void
    {
        Course::query()->whereKey($course)->update([
            'enrollment_count' => CourseEnrollment::query()
                ->where('course_id', $course->id)
                ->whereIn('status', CourseEnrollment::ACTIVE_STATUSES)
                ->count(),
        ]);
    }
}
