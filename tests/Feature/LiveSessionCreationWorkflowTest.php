<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\TeacherClassroom\Models\Classroom;
use Mindigo\TeacherClassroom\Models\ClassroomSchedule;
use Mindigo\TeacherCourse\Models\Course;
use Mindigo\TeacherLiveSession\Enums\LiveSessionType;
use Mindigo\TeacherLiveSession\Models\LiveSession;
use Tests\TestCase;

class LiveSessionCreationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_page_exposes_course_context_and_only_enabled_provider(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->courseClassroom($teacher->id);
        $schedule = $this->schedule($classroom, ClassroomSchedule::TYPE_REGULAR);

        $response = $this->actingAs($teacher)->get(route('teacher.live-sessions.create'));

        $response->assertOk();
        $response->assertSee('Mindigo Live');
        $response->assertSee($schedule->title);
        $response->assertSee('Google Meet');
        $response->assertSee('disabled', false);
        $response->assertSee('data-create-wizard="1"', false);
        $response->assertSee('data-live-session-form-next', false);
        $response->assertSee('data-live-session-form-submit hidden', false);
    }

    public function test_edit_page_keeps_all_form_tabs_available(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, Classroom::TYPE_STANDALONE, null, 'EDIT-TABS');
        $session = LiveSession::query()->create([
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'created_by' => $teacher->id,
            'title' => 'Editable live lesson',
            'room_name' => 'edit-tabs-'.uniqid(),
            'provider' => 'native',
            'fallback_provider' => 'native',
            'provider_status' => 'created',
            'sync_status' => 'not_required',
            'session_type' => 'flexible',
            'scheduled_start' => now()->addDay(),
            'scheduled_end' => now()->addDay()->addHour(),
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($teacher)->get(route('teacher.live-sessions.edit', $session));

        $response->assertOk();
        $response->assertSee('data-create-wizard="0"', false);
        $response->assertSee('data-live-session-form-submit', false);
    }

    public function test_course_class_requires_an_eligible_schedule(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->courseClassroom($teacher->id);

        $response = $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), $this->payload($classroom));

        $response->assertSessionHasErrors('classroom_schedule_id');
        $this->assertDatabaseCount('live_sessions', 0);
    }

    public function test_course_class_session_inherits_schedule_type_and_exact_time(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->courseClassroom($teacher->id);
        $schedule = $this->schedule($classroom, ClassroomSchedule::TYPE_REGULAR);

        $payload = $this->payload($classroom, [
            'classroom_schedule_id' => $schedule->id,
            'session_type' => LiveSessionType::Regular->value,
            'scheduled_start' => $schedule->session_date->format('Y-m-d').' 08:00:00',
            'scheduled_end' => $schedule->session_date->format('Y-m-d').' 10:00:00',
        ]);

        $response = $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), $payload);

        $response->assertRedirect(route('teacher.live-sessions.index'));
        $session = LiveSession::query()->sole();
        $this->assertSame($schedule->id, $session->classroom_schedule_id);
        $this->assertSame(LiveSessionType::Regular, $session->session_type);
        $this->assertTrue($session->room_settings['waiting_room_enabled']);
        $this->assertFalse($session->room_settings['student_screen_share_enabled']);
        $this->assertSame(ClassroomSchedule::DELIVERY_ONLINE, $schedule->fresh()->delivery_mode);
    }

    public function test_course_class_rejects_time_that_differs_from_its_schedule(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->courseClassroom($teacher->id);
        $schedule = $this->schedule($classroom, ClassroomSchedule::TYPE_REGULAR);

        $response = $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), $this->payload($classroom, [
            'classroom_schedule_id' => $schedule->id,
            'session_type' => LiveSessionType::Regular->value,
            'scheduled_start' => $schedule->session_date->format('Y-m-d').' 09:00:00',
            'scheduled_end' => $schedule->session_date->format('Y-m-d').' 11:00:00',
        ]));

        $response->assertSessionHasErrors(['scheduled_start', 'scheduled_end']);
        $this->assertDatabaseCount('live_sessions', 0);
    }

    public function test_standalone_class_is_flexible_and_rejects_overlapping_live_rooms(): void
    {
        $teacher = $this->createUser(['role' => 'teacher']);
        $classroom = $this->classroom($teacher->id, Classroom::TYPE_STANDALONE, null, 'STANDALONE');
        $payload = $this->payload($classroom);

        $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), $payload)->assertRedirect();
        $this->actingAs($teacher)->post(route('teacher.live-sessions.store'), [
            ...$payload,
            'title' => 'Overlapping room',
        ])->assertSessionHasErrors('scheduled_start');

        $this->assertDatabaseCount('live_sessions', 1);
        $this->assertSame(LiveSessionType::Flexible, LiveSession::query()->sole()->session_type);
    }

    private function payload(Classroom $classroom, array $overrides = []): array
    {
        return [
            'title' => 'Live lesson',
            'classroom_id' => $classroom->id,
            'provider' => 'native',
            'scheduled_start' => now()->addDays(3)->setTime(8, 0)->format('Y-m-d H:i:s'),
            'scheduled_end' => now()->addDays(3)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'room_settings' => [
                'waiting_room_enabled' => 1,
                'guest_access_enabled' => 0,
                'chat_enabled' => 1,
                'private_chat_enabled' => 0,
                'student_microphone_enabled' => 1,
                'student_camera_enabled' => 1,
                'student_screen_share_enabled' => 0,
                'recording_enabled' => 0,
            ],
            ...$overrides,
        ];
    }

    private function courseClassroom(int $teacherId): Classroom
    {
        $course = Course::query()->create([
            'teacher_id' => $teacherId,
            'name' => 'Course live',
            'slug' => 'course-live-'.uniqid(),
            'status' => 'active',
            'publication_status' => Course::PUBLICATION_PUBLISHED,
            'is_active' => true,
        ]);

        return $this->classroom($teacherId, Classroom::TYPE_COURSE, $course->id, 'COURSE');
    }

    private function classroom(int $teacherId, string $type, ?int $courseId, string $code): Classroom
    {
        return Classroom::query()->create([
            'created_by' => $teacherId,
            'teacher_id' => $teacherId,
            'type' => $type,
            'course_id' => $courseId,
            'name' => $code,
            'code' => $code,
            'slug' => strtolower($code).'-'.uniqid(),
            'status' => 'active',
        ]);
    }

    private function schedule(Classroom $classroom, string $type): ClassroomSchedule
    {
        return ClassroomSchedule::query()->create([
            'classroom_id' => $classroom->id,
            'type' => $type,
            'delivery_mode' => ClassroomSchedule::DELIVERY_OFFLINE,
            'status' => ClassroomSchedule::STATUS_SCHEDULED,
            'title' => 'Course schedule',
            'session_date' => now()->addDays(3)->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);
    }
}
