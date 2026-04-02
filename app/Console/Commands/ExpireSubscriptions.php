<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExpireSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire subscriptions whose end date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expired = Subscription::where('end_date', '<', date('Y-m-d'))
            ->where('status', 'Active')
            ->update([
                'status' => 'Inactive'
            ]);
        Cache::forget('user_subscriptions_all');
        Cache::forget('user_subscriptions_active');
        $this->info("Expired Subscriptions Updated: " . $expired);
    }
}
