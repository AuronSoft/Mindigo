<?php

namespace App\Jobs;

use Mindigo\BlogManagement\Models\NewsArticle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FetchNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $sources = [
        'vnexpress' => [
            'url'  => 'https://vnexpress.net/rss/giao-duc.rss',
            'name' => 'VnExpress',
        ],
        'thanhnien' => [
            'url'  => 'https://thanhnien.vn/rss/giao-duc.rss',
            'name' => 'Thanh Niên',
        ],
        'tuoitre' => [
            'url'  => 'https://tuoitre.vn/rss/giao-duc.rss',
            'name' => 'Tuổi Trẻ',
        ],
    ];

    public function handle(): void
    {
        foreach ($this->sources as $key => $source) {
            try {
                $xml = simplexml_load_file($source['url']);
                if (!$xml) continue;

                foreach ($xml->channel->item as $item) {
                    $url = (string) $item->link;
                    if (NewsArticle::where('url', $url)->exists()) continue;

                    // Lấy ảnh từ enclosure hoặc media:content hoặc description
                    $image = null;
                    if (isset($item->enclosure['url'])) {
                        $image = (string) $item->enclosure['url'];
                    } elseif (isset($item->children('media', true)->content)) {
                        $image = (string) $item->children('media', true)->content->attributes()['url'];
                    } else {
                        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) $item->description, $matches);
                        $image = $matches[1] ?? null;
                    }

                    $title = (string) $item->title;

                    NewsArticle::create([
                        'title'        => $title,
                        'slug'         => Str::slug($title) . '-' . Str::random(6),
                        'description'  => strip_tags((string) $item->description),
                        'image'        => $image,
                        'url'          => $url,
                        'source'       => $source['name'],
                        'category'     => 'Giáo dục',
                        'published_at' => isset($item->pubDate) ? \Carbon\Carbon::parse((string) $item->pubDate) : now(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("FetchNewsJob error [{$key}]: " . $e->getMessage());
            }
        }
    }
}