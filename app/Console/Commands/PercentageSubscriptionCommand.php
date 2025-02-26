<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Notification;

class PercentageSubscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:percentage-subscription-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends email to users who have subscribed for price drop/jump within X amount of hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Notification::processPercentageSubscriptions();

        return true;
    }
}
