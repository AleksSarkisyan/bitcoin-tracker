<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Notification;

class PriceSubscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:price-subscription-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets the current BTC price from Bitfinix API and notifies users if price is above their target.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Notification::processPriceSubscriptions();

        return true;
    }
}
