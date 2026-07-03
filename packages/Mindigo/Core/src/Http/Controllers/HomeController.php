<?php

namespace Mindigo\Core\Http\Controllers;

use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('core::home');
    }

    public function terms()
    {
        return view('core::legal.terms', [
            'title' => __('core::terms.hero.title') . ' | Mindigo',
        ]);
    }

    public function privacy()
    {
        return view('core::legal.privacy', [
            'title' => __('core::privacy.hero.title') . ' | Mindigo',
        ]);
    }

    public function technicalSupportPolicy()
    {
        return view('core::legal.technical-support', [
            'title' => __('core::technical_support.hero.title') . ' | Mindigo',
        ]);
    }

    public function aiAssistantPolicy()
    {
        return view('core::legal.ai-assistant-policy', [
            'title' => __('core::ai_policy.hero.title') . ' | Mindigo',
        ]);
    }
}
