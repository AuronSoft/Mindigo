<?php

namespace App\Console\Commands\Core;

use App\Jobs\FetchNewsJob;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    protected $signature   = 'news:fetch';
    protected $description = 'Fetch news articles from RSS';

    public function handle(): void
    {
        $this->info('Fetching news...');
        dispatch_sync(new FetchNewsJob);
        $this->info('Done!');
    }
}