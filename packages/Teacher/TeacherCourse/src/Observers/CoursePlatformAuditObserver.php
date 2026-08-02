<?php

namespace Mindigo\TeacherCourse\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mindigo\AuditLog\Services\AuditLogService;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseClassroomAssignment;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseReview;
use Mindigo\TeacherCourse\Models\CourseWishlist;

class CoursePlatformAuditObserver
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function created(Model $model): void
    {
        $this->audit->record($this->createdAction($model), 'course-platform', newValues: $model->getAttributes(), auditable: $model);
        $this->forgetAnalytics($model);
    }

    public function updated(Model $model): void
    {
        $action = match (true) {
            $model instanceof Course && $model->wasChanged('publication_status') => $model->publication_status === Course::PUBLICATION_PUBLISHED ? 'course_published' : 'course_unpublished',
            $model instanceof CourseEnrollment && $model->wasChanged('status') && $model->status === CourseEnrollment::STATUS_COMPLETED => 'course_completed',
            $model instanceof CourseReview => 'course_review_updated',
            default => 'course_updated',
        };

        $this->audit->record($action, 'course-platform', oldValues: array_intersect_key($model->getOriginal(), $model->getChanges()), newValues: $model->getChanges(), auditable: $model);
        $this->forgetAnalytics($model);
    }

    public function deleted(Model $model): void
    {
        $action = $model instanceof CourseWishlist ? 'course_wishlist_removed' : 'course_deleted';
        $this->audit->record($action, 'course-platform', oldValues: $model->getOriginal(), auditable: $model);
        $this->forgetAnalytics($model);
    }

    private function createdAction(Model $model): string
    {
        return match (true) {
            $model instanceof CourseEnrollment => 'course_enrolled',
            $model instanceof CourseReview => 'course_review_created',
            $model instanceof CourseWishlist => 'course_wishlist_added',
            $model instanceof CourseClassroomAssignment => 'course_assigned',
            $model instanceof Course => 'course_created',
            default => 'course_activity_created',
        };
    }

    private function forgetAnalytics(Model $model): void
    {
        Cache::forget('course:analytics:admin');
        $teacherId = match (true) {
            $model instanceof Course => $model->teacher_id,
            $model instanceof CourseEnrollment, $model instanceof CourseReview, $model instanceof CourseWishlist, $model instanceof CourseClassroomAssignment => Course::query()->whereKey($model->course_id)->value('teacher_id'),
            default => null,
        };
        if ($teacherId) {
            Cache::forget('course:analytics:teacher:'.$teacherId);
        }
    }
}
