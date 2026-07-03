<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mindigo\Auth\Models\User;
use Mindigo\Core\Models\ExamTipPost;

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
     * Homepage CTA and footer use LMS positioning
     */
    public function test_homepage_cta_and_footer_use_lms_positioning(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('LMS', false)
            ->assertSee('Khởi tạo LMS', false)
            ->assertSee('Quản lý lớp học, khóa học', false)
            ->assertDontSee('Tạo nhanh đề thi trắc nghiệm', false)
            ->assertDontSee('Nền tảng thi trắc nghiệm online tốt nhất', false);
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
        $user = User::factory()->create(['name' => 'Nguyen Minh Anh']);

        ExamTipPost::create([
            'user_id' => $user->id,
            'title' => 'Lich hoc that tu cong dong',
            'category' => 'toan',
            'excerpt' => 'Mot bai chia se that duoc doc tu database.',
            'content' => 'Mot bai chia se that duoc doc tu database voi noi dung du dai cho trang cong dong.',
            'tags' => ['luyen de', 'toan'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get('/exam-tips');

        $response->assertOk();
        $response->assertSee('Mindigo', false);
        $response->assertDontSee('Mindigo.vn', false);
        $response->assertSee('data-exam-tip-card', false);
        $response->assertSee('Lich hoc that tu cong dong', false);
        $response->assertSee('data-exam-tip-share-login', false);
        $response->assertSee('data-exam-tip-login-link', false);
        $response->assertSee('href="/login"', false);
    }

    /**
     * Authenticated user can create an exam tip post
     */
    public function test_authenticated_user_can_create_exam_tip_post(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)->post('/exam-tips', [
            'title' => 'Cach on thi tu du lieu that',
            'category' => 'anh',
            'content' => 'Day la noi dung chia se kinh nghiem on thi du dai de vuot qua validation cua cong dong.',
            'tags' => 'IELTS, speaking',
        ])->assertRedirect('/exam-tips');

        $this->assertDatabaseHas('exam_tip_posts', [
            'user_id' => $user->id,
            'title' => 'Cach on thi tu du lieu that',
            'category' => 'anh',
            'status' => 'published',
        ]);
    }

    /**
     * Exam tips header shows the authenticated user indicator
     */
    public function test_exam_tips_header_shows_user_indicator_when_authenticated(): void
    {
        $user = User::factory()->create([
            'name' => 'Nguyen Minh Anh',
            'role' => 'student',
        ]);

        $this->actingAs($user)->get('/exam-tips')
            ->assertOk()
            ->assertSee('data-exam-tip-share-action', false)
            ->assertSee('data-exam-tip-user-menu', false)
            ->assertSee('Nguyen Minh Anh', false)
            ->assertSee('exam-tip-share-title', false)
            ->assertDontSee('data-exam-tip-share-login', false)
            ->assertDontSee('data-exam-tip-login-link', false);
    }

    /**
     * Exam tips page follows selected locale
     */
    public function test_exam_tips_page_follows_selected_locale(): void
    {
        $user = User::factory()->create(['name' => 'Tran Bao Chau']);

        ExamTipPost::create([
            'user_id' => $user->id,
            'title' => 'Real IELTS journey',
            'category' => 'anh',
            'excerpt' => 'A real post from database for locale rendering.',
            'content' => 'A real post from database for locale rendering with enough words to be displayed correctly.',
            'tags' => ['IELTS'],
            'status' => 'published',
            'published_at' => now(),
        ]);

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
