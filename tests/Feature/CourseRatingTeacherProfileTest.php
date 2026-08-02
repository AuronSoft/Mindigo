<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherCourse\Models\CourseEnrollment;
use Mindigo\TeacherCourse\Models\CourseReview;
use Mindigo\TeacherCourse\Models\TeacherProfile;
use Tests\TestCase;

class CourseRatingTeacherProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_eligible_student_can_review_a_course(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $enrollment = $this->enrollment($course, $student->id, 80);

        $this->actingAs($student)->post(route('courses.reviews.store', $course), ['rating' => 5, 'comment' => 'Very useful course'])->assertSessionHasErrors('review');
        $enrollment->update(['status' => CourseEnrollment::STATUS_COMPLETED, 'completion_percentage' => 100]);
        $this->actingAs($student)->post(route('courses.reviews.store', $course), ['rating' => 5, 'comment' => 'Very useful course'])->assertRedirect();

        $this->assertDatabaseHas('course_reviews', ['enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'rating' => 5]);
    }

    public function test_unenrolled_student_and_teacher_cannot_review(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $teacher = $this->createUser(['role' => 'teacher']);
        $course = $this->course($teacher->id);

        $this->actingAs($student)->post(route('courses.reviews.store', $course), ['rating' => 4, 'comment' => 'Not enrolled'])->assertSessionHasErrors('review');
        $this->actingAs($teacher)->post(route('courses.reviews.store', $course), ['rating' => 5, 'comment' => 'Self review'])->assertRedirect();
        $this->assertDatabaseCount('course_reviews', 0);
    }

    public function test_repeated_submission_updates_one_review_and_rating_aggregate(): void
    {
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $this->enrollment($course, $student->id, 100, CourseEnrollment::STATUS_COMPLETED);

        $this->actingAs($student)->post(route('courses.reviews.store', $course), ['rating' => 3, 'comment' => 'First review'])->assertRedirect();
        $this->actingAs($student)->post(route('courses.reviews.store', $course), ['rating' => 5, 'comment' => 'Updated review'])->assertRedirect();

        $this->assertDatabaseCount('course_reviews', 1);
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'rating_count' => 1, 'rating_average' => 5]);
    }

    public function test_student_can_only_edit_own_review_inside_its_course(): void
    {
        $owner = $this->createUser(['role' => 'student']);
        $outsider = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $enrollment = $this->enrollment($course, $owner->id, 100, CourseEnrollment::STATUS_COMPLETED);
        $review = CourseReview::query()->create(['course_id' => $course->id, 'enrollment_id' => $enrollment->id, 'student_id' => $owner->id, 'rating' => 4]);

        $this->actingAs($outsider)->put(route('courses.reviews.update', [$course, $review]), ['rating' => 1, 'comment' => 'Injected'])->assertForbidden();
        $this->actingAs($owner)->put(route('courses.reviews.update', [$course, $review]), ['rating' => 5, 'comment' => 'My update'])->assertRedirect();
        $this->assertSame(5, $review->refresh()->rating);
    }

    public function test_only_course_owner_can_reply_to_review(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher->id);
        $enrollment = $this->enrollment($course, $student->id, 100, CourseEnrollment::STATUS_COMPLETED);
        $review = CourseReview::query()->create(['course_id' => $course->id, 'enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'rating' => 5]);

        $this->actingAs($outsider)->post(route('course-reviews.reply', $review), ['teacher_reply' => 'Unauthorized'])->assertForbidden();
        $this->actingAs($teacher)->post(route('course-reviews.reply', $review), ['teacher_reply' => 'Thank you for learning'])->assertRedirect();
        $this->assertDatabaseHas('course_reviews', ['id' => $review->id, 'teacher_reply' => 'Thank you for learning', 'replied_by' => $teacher->id]);
    }

    public function test_admin_can_hide_and_restore_review_and_rating_is_recalculated(): void
    {
        $admin = $this->createUser(['role' => 'admin']);
        $student = $this->createUser(['role' => 'student']);
        $course = $this->course();
        $enrollment = $this->enrollment($course, $student->id, 100, CourseEnrollment::STATUS_COMPLETED);
        $review = CourseReview::query()->create(['course_id' => $course->id, 'enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'rating' => 5]);

        $this->actingAs($admin)->patch(route('admin.course-reviews.moderate', $review), ['moderation_status' => 'hidden', 'moderation_reason' => 'Policy violation'])->assertRedirect();
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'rating_count' => 0]);
        $this->actingAs($admin)->patch(route('admin.course-reviews.moderate', $review), ['moderation_status' => 'visible'])->assertRedirect();
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'rating_count' => 1, 'rating_average' => 5]);
    }

    public function test_course_detail_displays_visible_reviews_distribution_and_reply(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $student = $this->createUser(['role' => 'student']);
        $viewer = $this->createUser(['role' => 'student']);
        $course = $this->course($teacher->id);
        $enrollment = $this->enrollment($course, $student->id, 100, CourseEnrollment::STATUS_COMPLETED);
        CourseReview::query()->create(['course_id' => $course->id, 'enrollment_id' => $enrollment->id, 'student_id' => $student->id, 'rating' => 5, 'comment' => 'Excellent curriculum', 'teacher_reply' => 'Thank you']);
        $course->update(['rating_average' => 5, 'rating_count' => 1]);

        $this->actingAs($viewer)->get(route('courses.show', $course->slug))->assertOk()->assertSee('Excellent curriculum')->assertSee('Thank you')->assertSee('5 ★');
    }

    public function test_public_teacher_profile_only_shows_public_profile_and_published_courses(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $profile = TeacherProfile::query()->create(['user_id' => $teacher->id, 'headline' => 'Mathematics educator', 'biography' => 'Ten years of teaching', 'specialization' => 'Algebra', 'experience_years' => 10, 'is_public' => true]);
        $published = $this->course($teacher->id);
        $draft = $this->course($teacher->id, Course::PUBLICATION_DRAFT);

        $this->get(route('teachers.show', $teacher))->assertOk()->assertSee($profile->headline)->assertSee($published->name)->assertDontSee($draft->name);
        $profile->update(['is_public' => false]);
        $this->get(route('teachers.show', $teacher))->assertNotFound();
    }

    public function test_teacher_can_update_only_own_public_profile(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $outsider = $this->createUser(['role' => 'teacher']);
        $profile = TeacherProfile::query()->create(['user_id' => $teacher->id]);
        $payload = ['headline' => 'Science mentor', 'biography' => 'Experienced educator', 'specialization' => 'Physics', 'experience_years' => 8, 'qualifications' => "Degree A\nCertificate B", 'is_public' => true];

        $this->actingAs($outsider)->put(route('teacher.profile.update', $profile), $payload)->assertForbidden();
        $this->actingAs($teacher)->put(route('teacher.profile.update', $profile), $payload)->assertRedirect();
        $this->assertDatabaseHas('teacher_profiles', ['id' => $profile->id, 'headline' => 'Science mentor', 'is_public' => true]);
        $this->assertSame(['Degree A', 'Certificate B'], $profile->refresh()->qualifications);
    }

    private function course(?int $teacherId = null, string $status = Course::PUBLICATION_PUBLISHED): Course
    {
        $teacherId ??= $this->createUser(['role' => 'teacher'])->id;

        return Course::query()->create(['teacher_id' => $teacherId, 'name' => 'Course '.str()->random(8), 'slug' => 'course-'.str()->lower(str()->random(10)), 'status' => 'active', 'is_active' => true, 'publication_status' => $status, 'published_at' => $status === Course::PUBLICATION_PUBLISHED ? now() : null, 'difficulty' => 'beginner', 'language' => 'vi', 'access_type' => 'free']);
    }

    private function enrollment(Course $course, int $studentId, int $percentage, string $status = CourseEnrollment::STATUS_IN_PROGRESS): CourseEnrollment
    {
        return CourseEnrollment::query()->create(['course_id' => $course->id, 'student_id' => $studentId, 'status' => $status, 'source' => 'self', 'completion_percentage' => $percentage, 'enrolled_at' => now()]);
    }
}
