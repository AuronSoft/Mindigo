<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Homepage accessible
     */
    public function test_homepage_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Guest can access homepage
     */
    public function test_guest_can_access_homepage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    /**
     * News page accessible
     */
    public function test_news_page_is_accessible(): void
    {
        $response = $this->get('/news');

        $response->assertOk();
        $response->assertSee('id="news-articles"', false);
    }

    /**
     * Footer news resource points to news route
     */
    public function test_footer_news_resource_points_to_news_route(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="/news"', false);
    }

    /**
     * Exam tips page accessible
     */
    public function test_exam_tips_page_is_accessible(): void
    {
        $response = $this->get('/exam-tips');

        $response->assertOk();
        $response->assertSee('Mindigo', false);
        $response->assertDontSee('Mindigo.vn', false);
        $response->assertSee('data-exam-tip-card', false);
        $response->assertSee('href="/login"', false);
    }

    /**
     * Exam tips page follows selected locale
     */
    public function test_exam_tips_page_follows_selected_locale(): void
    {
        $this->withSession(['locale' => 'en'])->get('/exam-tips')
            ->assertOk()
            ->assertSee('Exam tips', false)
            ->assertSee('Featured today', false)
            ->assertSee('from people who have been there', false)
            ->assertSee('Sign in to comment', false)
            ->assertSee('Sign in to share a post', false);
    }

    /**
     * Footer exam tips resource points to exam tips route
     */
    public function test_footer_exam_tips_resource_points_to_exam_tips_route(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="/exam-tips"', false);
    }

    /**
     * Terms page accessible
     */
    public function test_terms_page_is_accessible(): void
    {
        $response = $this->get('/terms');

        $response->assertOk();
        $response->assertSee('Điều khoản Sử dụng', false);
        $response->assertSee('Google, Apple, Microsoft', false);
    }

    /**
     * Terms page follows selected locale
     */
    public function test_terms_page_follows_selected_locale(): void
    {
        $this->withSession(['locale' => 'en'])->get('/terms')
            ->assertOk()
            ->assertSee('Terms of Use', false)
            ->assertSee('Table of Contents', false)
            ->assertDontSee('Điều khoản Sử dụng', false);
    }

    /**
     * Privacy page accessible
     */
    public function test_privacy_page_is_accessible(): void
    {
        $response = $this->get('/privacy');

        $response->assertOk();
        $response->assertSee('Google, Apple, Microsoft', false);
        $response->assertSee('privacy@mindigo.vn', false);
    }

    /**
     * Privacy page follows selected locale
     */
    public function test_privacy_page_follows_selected_locale(): void
    {
        $this->withSession(['locale' => 'en'])->get('/privacy')
            ->assertOk()
            ->assertSee('Privacy Policy', false)
            ->assertSee('Table of Contents', false)
            ->assertSee('Google, Apple, and Microsoft Sign-in', false);
    }

    /**
     * Technical support policy page accessible
     */
    public function test_technical_support_policy_page_is_accessible(): void
    {
        $response = $this->get('/technical-support-policy');

        $response->assertOk();
        $response->assertSee('support@mindigo.vn', false);
        $response->assertSee('Mindigo ID', false);
    }

    /**
     * Technical support policy page follows selected locale
     */
    public function test_technical_support_policy_page_follows_selected_locale(): void
    {
        $this->withSession(['locale' => 'en'])->get('/technical-support-policy')
            ->assertOk()
            ->assertSee('Technical Support Terms', false)
            ->assertSee('Priority Levels and Response Times', false)
            ->assertSee('Table of Contents', false);
    }

    /**
     * AI assistant policy page accessible
     */
    public function test_ai_assistant_policy_page_is_accessible(): void
    {
        $response = $this->get('/ai-assistant-policy');

        $response->assertOk();
        $response->assertSee('support@mindigo.vn', false);
        $response->assertSee('AI', false);
    }

    /**
     * AI assistant policy page follows selected locale
     */
    public function test_ai_assistant_policy_page_follows_selected_locale(): void
    {
        $this->withSession(['locale' => 'en'])->get('/ai-assistant-policy')
            ->assertOk()
            ->assertSee('AI Assistant Usage Policy', false)
            ->assertSee('Academic Integrity', false)
            ->assertSee('Table of Contents', false);
    }

    /**
     * Refund policy page accessible
     */
    public function test_refund_policy_page_is_accessible(): void
    {
        $response = $this->get('/refund-policy');

        $response->assertOk();
        $response->assertSee('support@mindigo.vn', false);
        $response->assertSee('Mindigo', false);
    }

    /**
     * Refund policy page follows selected locale
     */
    public function test_refund_policy_page_follows_selected_locale(): void
    {
        $this->withSession(['locale' => 'en'])->get('/refund-policy')
            ->assertOk()
            ->assertSee('Refund Policy', false)
            ->assertSee('Refund-eligible Cases', false)
            ->assertSee('Table of Contents', false);
    }

    /**
     * Locale switch updates public pages and login consistently
     */
    public function test_locale_switch_persists_for_homepage_and_login(): void
    {
        $response = $this->from('/')->get('/lang/en');

        $response->assertRedirect('/');
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('locale', 'en');

        $this->withSession(['locale' => 'en'])->get('/')
            ->assertOk()
            ->assertSee('Features', false)
            ->assertSee('Send us a message', false);

        $this->withSession(['locale' => 'en'])->get('/login')
            ->assertOk()
            ->assertSee('Sign in', false)
            ->assertSee('Mindingo LMS Platform', false);
    }

    /**
     * Authenticated user can access homepage
     */
    public function test_authenticated_user_can_access_homepage(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/');

        $response->assertOk();
    }

    /**
     * User factory creates valid user
     */
    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
        ]);
    }

    /**
     * Admin role is assigned correctly
     */
    public function test_admin_role_is_assigned_correctly(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Student role is assigned correctly
     */
    public function test_student_role_is_assigned_correctly(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->assertTrue($student->isStudent());
    }
}
