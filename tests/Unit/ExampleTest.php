<?php

namespace Tests\Unit;

use Mindigo\Auth\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    #[Test]
    public function application_environment_is_working(): void
    {
        $this->assertTrue(true);
    }

    #[Test]
    public function user_model_has_correct_roles(): void
    {
        $roles = User::ROLES;

        $this->assertArrayHasKey('admin', $roles);
        $this->assertArrayHasKey('teacher', $roles);
        $this->assertArrayHasKey('student', $roles);

        $this->assertEquals('Administrator', $roles['admin']);
        $this->assertEquals('Teacher', $roles['teacher']);
        $this->assertEquals('Student', $roles['student']);
    }

    #[Test]
    public function user_model_has_correct_gender_values(): void
    {
        $genders = User::GENDERS;

        $this->assertArrayHasKey('male', $genders);
        $this->assertArrayHasKey('female', $genders);
        $this->assertArrayHasKey('other', $genders);
    }

    #[Test]
    public function user_can_check_admin_role(): void
    {
        $user = new User([
            'role' => 'admin',
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isTeacher());
        $this->assertFalse($user->isStudent());
    }

    #[Test]
    public function user_can_check_teacher_role(): void
    {
        $user = new User([
            'role' => 'teacher',
        ]);

        $this->assertTrue($user->isTeacher());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isStudent());
    }

    #[Test]
    public function user_can_check_student_role(): void
    {
        $user = new User([
            'role' => 'student',
        ]);

        $this->assertTrue($user->isStudent());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isTeacher());
    }

    #[Test]
    public function user_has_default_avatar_url(): void
    {
        $user = new User([
            'name' => 'Mindigo User',
        ]);

        $this->assertStringContainsString(
            'ui-avatars.com',
            $user->avatar_url
        );
    }

    #[Test]
    public function user_can_generate_role_label(): void
    {
        $user = new User([
            'role' => 'teacher',
        ]);

        $this->assertEquals(
            'Teacher',
            $user->role_label
        );
    }

    #[Test]
    public function password_is_hidden(): void
    {
        $user = new User([
            'name' => 'Admin',
            'email' => 'admin@mindigo.com',
            'password' => '123456',
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }
}