<?php

namespace Mindigo\BlogManagement\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'image',
        'url', 'source', 'category', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
