<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\AssetPrice;

class CreateAssetPriceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-asset-price-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calls Bitfinex API to get the current price of BTC and stores it in the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        AssetPrice::create();

        return true;
    }
}
