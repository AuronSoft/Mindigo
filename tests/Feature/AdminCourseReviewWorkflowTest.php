<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mindigo\Auth\Models\User;
use Mindigo\Notification\Notifications\CourseReviewDecision;
use Mindigo\Notification\Notifications\CourseSubmittedForReview;
use Mindigo\TeacherCourse\Models\Chapter;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseReviewHistory;
use Mindigo\TeacherCourse\Models\Lesson;
use Tests\TestCase;

class AdminCourseReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_submission_creates_queue_history_and_notifies_active_admins(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);
        $inactiveAdmin = $this->createUser(['role' => 'admin', 'is_active' => false]);
        $course = $this->course($teacher);

        $this->actingAs($teacher)->patch(route('teacher.courses.publication.update', $course), [
            'publication_status' => Course::PUBLICATION_PENDING_REVIEW,
        ])->assertRedirect(route('teacher.courses.show', $course));

        $this->assertDatabaseHas('course_review_histories', [
            'course_id' => $course->id,
            'review_status' => CourseReviewHistory::STATUS_PENDING,
            'reviewer_id' => null,
            'publication_state_before' => Course::PUBLICATION_DRAFT,
            'publication_state_after' => Course::PUBLICATION_PENDING_REVIEW,
        ]);
        Notification::assertSentTo($admin, CourseSubmittedForReview::class);
        Notification::assertNotSentTo($inactiveAdmin, CourseSubmittedForReview::class);
    }

    public function test_admin_queue_only_contains_pending_courses_and_supports_search_filter_and_sort(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $firstTeacher = $this->createUser(['role' => 'teacher', 'name' => 'Teacher Alpha']);
        $secondTeacher = $this->createUser(['role' => 'teacher', 'name' => 'Teacher Beta']);
        $matched = $this->course($firstTeacher, ['name' => 'Algebra Review', 'publication_status' => Course::PUBLICATION_PENDING_REVIEW, 'submitted_for_review_at' => now()->subDay()]);
        $this->course($secondTeacher, ['name' => 'Physics Review', 'publication_status' => Course::PUBLICATION_PENDING_REVIEW, 'submitted_for_review_at' => now()]);
        $this->course($firstTeacher, ['name' => 'Algebra Draft']);

        $this->actingAs($admin)->get(route('admin.course-publication-reviews.index', [
            'search' => 'Algebra',
            'teacher_id' => $firstTeacher->id,
            'sort' => 'oldest',
        ]))->assertOk()
            ->assertSee($matched->name)
            ->assertDontSee('Physics Review')
            ->assertDontSee('Algebra Draft')
            ->assertSee('data-mindigo-drawer-panel="admin-course-review-filter"', false);
    }

    public function test_admin_can_approve_pending_course_and_teacher_is_notified(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);
        $course = $this->course($teacher, ['publication_status' => Course::PUBLICATION_PENDING_REVIEW, 'submitted_for_review_at' => now()]);

        $this->actingAs($admin)->patch(route('admin.course-publication-reviews.update', $course), [
            'action' => 'approve',
            'review_note' => 'Course content meets publication requirements.',
        ])->assertRedirect(route('admin.course-publication-reviews.index'));

        $course->refresh();
        $this->assertSame(Course::PUBLICATION_PUBLISHED, $course->publication_status);
        $this->assertSame($admin->id, $course->published_by);
        $this->assertDatabaseHas('course_review_histories', [
            'course_id' => $course->id,
            'reviewer_id' => $admin->id,
            'review_status' => CourseReviewHistory::STATUS_APPROVED,
            'publication_state_after' => Course::PUBLICATION_PUBLISHED,
        ]);
        Notification::assertSentTo($teacher, CourseReviewDecision::class);
    }

    public function test_request_changes_requires_reason_and_returns_course_to_draft(): void
    {
        Notification::fake();
        $teacher = $this->createUser(['role' => 'teacher']);
        $admin = $this->createUser(['role' => 'admin']);
        $course = $this->course($teacher, ['publication_status' => Course::PUBLICATION_PENDING_REVIEW, 'submitted_for_review_at' => now()]);

        $this->actingAs($admin)->from(route('admin.course-publication-reviews.show', $course))
            ->patch(route('admin.course-publication-reviews.update', $course), ['action' => 'request_changes'])
            ->assertSessionHasErrors('review_note');

        $this->actingAs($admin)->patch(route('admin.course-publication-reviews.update', $course), [
            'action' => 'request_changes',
            'review_note' => 'Please add learning outcomes and complete chapter one.',
        ])->assertRedirect(route('admin.course-publication-reviews.index'));

        $this->assertSame(Course::PUBLICATION_DRAFT, $course->fresh()->publication_status);
        $this->assertDatabaseHas('course_review_histories', [
            'course_id' => $course->id,
            'reviewer_id' => $admin->id,
            'review_status' => CourseReviewHistory::STATUS_CHANGES_REQUESTED,
            'review_note' => 'Please add learning outcomes and complete chapter one.',
        ]);
        Notification::assertSentTo($teacher, CourseReviewDecision::class);
    }

    public function test_non_admin_cannot_access_queue_or_review_another_course(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher, ['publication_status' => Course::PUBLICATION_PENDING_REVIEW]);

        foreach ([$teacher, $student] as $actor) {
            $this->actingAs($actor)->get(route('admin.course-publication-reviews.index'))->assertForbidden();
            $this->actingAs($actor)->patch(route('admin.course-publication-reviews.update', $course), ['action' => 'approve'])->assertForbidden();
        }
    }

    public function test_admin_cannot_review_course_outside_pending_state(): void
    {
        $admin = $this->createUser(['role' => 'admin']);

        foreach ([Course::PUBLICATION_DRAFT, Course::PUBLICATION_PUBLISHED, Course::PUBLICATION_ARCHIVED] as $status) {
            $course = $this->course($this->createUser(['role' => 'teacher']), ['publication_status' => $status]);
            $this->actingAs($admin)->patch(route('admin.course-publication-reviews.update', $course), ['action' => 'approve'])->assertForbidden();
        }
    }

    public function test_admin_dashboard_exposes_review_counters_and_queue(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $pending = $this->course($teacher, ['name' => 'Dashboard Pending Course', 'publication_status' => Course::PUBLICATION_PENDING_REVIEW, 'submitted_for_review_at' => now()]);
        CourseReviewHistory::query()->create([
            'course_id' => $pending->id,
            'reviewer_id' => $admin->id,
            'review_status' => CourseReviewHistory::STATUS_APPROVED,
            'publication_state_before' => Course::PUBLICATION_PENDING_REVIEW,
            'publication_state_after' => Course::PUBLICATION_PUBLISHED,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('teacher-course::admin-review.pending_dashboard'))
            ->assertSee(__('teacher-course::admin-review.approved_today'))
            ->assertSee($pending->name)
            ->assertSee(route('admin.course-publication-reviews.index'), false);
    }

    public function test_admin_can_preview_complete_curriculum_and_review_history(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher, ['publication_status' => Course::PUBLICATION_PENDING_REVIEW]);
        $chapter = Chapter::query()->create(['course_id' => $course->id, 'name' => 'Review Chapter']);
        Lesson::query()->create(['chapter_id' => $chapter->id, 'name' => 'Protected Lesson']);
        CourseReviewHistory::query()->create([
            'course_id' => $course->id,
            'review_status' => CourseReviewHistory::STATUS_PENDING,
            'publication_state_before' => Course::PUBLICATION_DRAFT,
            'publication_state_after' => Course::PUBLICATION_PENDING_REVIEW,
        ]);

        $this->actingAs($admin)->get(route('admin.course-publication-reviews.show', $course))
            ->assertOk()
            ->assertSee('Review Chapter')
            ->assertSee('Protected Lesson')
            ->assertSee(__('teacher-course::admin-review.history'))
            ->assertSee(__('teacher-course::admin-review.approve'))
            ->assertSee(__('teacher-course::admin-review.request_changes'));
    }

    private function course(User $teacher, array $attributes = []): Course
    {
        return Course::query()->create([
            'teacher_id' => $teacher->id,
            'name' => 'Course '.str()->random(8),
            'slug' => 'course-'.str()->lower(str()->random(12)),
            'status' => 'active',
            'is_active' => true,
            'publication_status' => Course::PUBLICATION_DRAFT,
            'difficulty' => 'beginner',
            'language' => 'vi',
            ...$attributes,
        ]);
    }
}
