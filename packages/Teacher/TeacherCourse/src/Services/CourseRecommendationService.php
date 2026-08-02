<?php

namespace Mindigo\TeacherCourse\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Mindigo\Auth\Models\User;
use Mindigo\ExamManagement\Models\ExamAttempt;
use Mindigo\StudentPractice\Models\StudentSkillProgress;
use Mindigo\SubjectManagement\Models\Subject;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseView;
use Mindigo\TeacherCourse\Models\CourseWishlist;

class CourseRecommendationService
{
    public function forStudent(User $student, ?int $limit = null): Collection
    {
        $limit ??= (int) config('course.discovery.section_limit', 8);

        return Cache::remember('courses:recommendations:user:'.$student->id, (int) config('course.discovery.cache_seconds', 600), function () use ($student, $limit): Collection {
            $historyCourseIds = CourseEnrollment::query()->where('student_id', $student->id)->pluck('course_id')
                ->merge(CourseWishlist::query()->where('user_id', $student->id)->pluck('course_id'))
                ->merge(CourseView::query()->where('user_id', $student->id)->pluck('course_id'))->unique();
            $history = Course::query()->whereKey($historyCourseIds)->get(['subject_id', 'category_id', 'education_level', 'difficulty', 'teacher_id']);
            $subjectIds = $history->pluck('subject_id')->filter()->unique();

            if (class_exists(StudentSkillProgress::class)) {
                $subjectIds = $subjectIds->merge(StudentSkillProgress::query()->where('student_id', $student->id)
                    ->where('mastery_score', '<', 70)->join('practice_skills', 'practice_skills.id', '=', 'student_skill_progress.practice_skill_id')
                    ->pluck('practice_skills.subject_id'))->filter()->unique();
            }

            $examSubjects = class_exists(ExamAttempt::class)
                ? ExamAttempt::query()->where('user_id', $student->id)->whereNotNull('percentage')->where('percentage', '<', 70)
                    ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')->pluck('exams.subject')->filter()->unique()
                : collect();
            if ($examSubjects->isNotEmpty()) {
                $subjectIds = $subjectIds->merge(Subject::query()->whereIn('name', $examSubjects)->pluck('id'))->unique();
            }

            $query = Course::query()->publiclyListed()
                ->whereNotIn('courses.id', CourseEnrollment::query()->where('student_id', $student->id)->select('course_id'))
                ->with(['teacher:id,name,avatar', 'subject:id,name', 'category:id,name'])->withCount('lessons');

            if ($history->isEmpty() && $subjectIds->isEmpty() && $examSubjects->isEmpty()) {
                return $query->orderByRaw('(enrollment_count * 4 + rating_count * 3 + view_count) DESC')->limit($limit)->get();
            }

            $categoryIds = $history->pluck('category_id')->filter()->unique()->all();
            $levels = $history->pluck('education_level')->filter()->unique()->all();
            $difficulties = $history->pluck('difficulty')->filter()->unique()->all();
            $teacherIds = $history->pluck('teacher_id')->filter()->unique()->all();

            $scores = [];
            $bindings = [];
            foreach ([[$subjectIds->all(), 'subject_id', 6], [$categoryIds, 'category_id', 4], [$levels, 'education_level', 3], [$difficulties, 'difficulty', 2], [$teacherIds, 'teacher_id', 1]] as [$values, $column, $weight]) {
                if ($values === []) {
                    continue;
                }
                $scores[] = 'CASE WHEN '.$column.' IN ('.implode(',', array_fill(0, count($values), '?')).') THEN '.$weight.' ELSE 0 END';
                array_push($bindings, ...$values);
            }

            return $query->orderByRaw('('.implode(' + ', $scores).') DESC', $bindings)
                ->orderByRaw('(rating_count * 3 + enrollment_count * 2 + view_count) DESC')->limit($limit)->get();
        });
    }
}
