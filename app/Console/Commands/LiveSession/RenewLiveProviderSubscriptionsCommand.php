<?php

namespace App\Console\Commands\LiveSession;

use Illuminate\Console\Command;
use Mindigo\TeacherLiveSession\Services\LiveProviderSubscriptionService;

final class RenewLiveProviderSubscriptionsCommand extends Command
{
    protected $signature = 'live-sessions:renew-provider-webhooks';

    protected $description = 'Register or renew expiring Google Calendar webhook channels';

    public function handle(LiveProviderSubscriptionService $subscriptions): int
    {
        $stats = $subscriptions->renewDue();
        $this->components->info("Renewed {$stats['renewed']} provider webhook channel(s); {$stats['failed']} failed.");

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
