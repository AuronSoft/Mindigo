<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mindigo\BlogManagement\Models\NewsArticle;

class FetchNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $sources = [
        'vnexpress' => [
            'url' => 'https://vnexpress.net/rss/giao-duc.rss',
            'name' => 'VnExpress',
        ],
        'thanhnien' => [
            'url' => 'https://thanhnien.vn/rss/giao-duc.rss',
            'name' => 'Thanh Niên',
        ],
        'tuoitre' => [
            'url' => 'https://tuoitre.vn/rss/giao-duc.rss',
            'name' => 'Tuổi Trẻ',
        ],
    ];

    public function handle(): void
    {
        // Xóa bài cũ hơn 30 ngày
        $deleted = NewsArticle::where('published_at', '<', now()->subDays(30))->delete();
        Log::info("FetchNewsJob: đã xóa {$deleted} bài cũ");

        foreach ($this->sources as $key => $source) {
            try {
                $xml = simplexml_load_file($source['url']);
                if (! $xml) {
                    Log::warning("FetchNewsJob: không load được RSS [{$key}]");

                    continue;
                }

                $count = 0;
                foreach ($xml->channel->item as $item) {
                    $url = (string) $item->link;
                    if (NewsArticle::where('url', $url)->exists()) {
                        continue;
                    }

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

                    // Decode HTML entities để hiển thị tiếng Việt đúng
                    $title = $this->decode((string) $item->title);
                    $description = $this->decode(strip_tags((string) $item->description));

                    NewsArticle::create([
                        'title' => $title,
                        'slug' => Str::slug($title).'-'.Str::random(6),
                        'description' => $description,
                        'image' => $image,
                        'url' => $url,
                        'source' => $source['name'],
                        'category' => 'Giáo dục',
                        'published_at' => isset($item->pubDate)
                                            ? Carbon::parse((string) $item->pubDate)
                                            : now(),
                    ]);

                    $count++;
                }

                Log::info("FetchNewsJob: [{$key}] thêm {$count} bài mới");

            } catch (\Exception $e) {
                Log::error("FetchNewsJob error [{$key}]: ".$e->getMessage());
            }
        }
    }

    /**
     * Decode HTML entities về text tiếng Việt chuẩn.
     */
    private function decode(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
