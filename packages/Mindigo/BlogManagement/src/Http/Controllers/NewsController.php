<?php

namespace Mindigo\BlogManagement\Http\Controllers;

use Mindigo\BlogManagement\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $source = $request->get('source', 'all');

        $query = NewsArticle::orderBy('published_at', 'desc');

        if ($source !== 'all') {
            $query->where('source', $source);
        }

        $featured = (clone $query)->first();
        $articles  = (clone $query)->skip(1)->take(11)->get();

        return view('core::pages.news', compact('featured', 'articles', 'source'));
    }

    public function partial(Request $request)
    {
        $source = $request->get('source', 'all');
        $query = NewsArticle::orderBy('published_at', 'desc');
        if ($source !== 'all') {
            $query->where('source', $source);
        }
        $newsfeatured = (clone $query)->first();
        $newsArticles = (clone $query)->skip(1)->take(11)->get();

        return view('blog::news-partial', compact('newsfeatured', 'newsArticles', 'source'));
    }
}