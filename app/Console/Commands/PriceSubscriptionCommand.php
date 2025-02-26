<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Bitfinex;
use App\Jobs\SendPriceNotificationJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use App\Repositories\SubscriptionRepositoryInterface;

class PriceSubscriptionCommand extends Command
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        parent::__construct();
        $this->subscriptionRepository = $subscriptionRepository;
    }

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
        $params = [
            'query' => 'tBTCUSD'
        ];

        $bitfinexApiData = Bitfinex::get($endpointType = 'ticker', $params);

        if (!$bitfinexApiData || isset($bitfinexApiData['error'])) {
            return false;
        }

        $currentPrice = intval($bitfinexApiData['last_price']);
        $subscribers = $this->subscriptionRepository->getPriceSubscribers($currentPrice);

        if (!$subscribers->count()) {
            Log::info('PriceSubscriptionCommand - Noting to process!');
            return false;
        }

        $chunkSize = 100;

        $subscribers->orderBy('id')
            ->chunkById($chunkSize, function ($subscribers) use ($bitfinexApiData) {
                Log::info('PriceSubscriptionCommand -Begin job processing for ' . $subscribers->count() . ' records');

                $priceNotificationJobs = [];
                foreach ($subscribers as $subscriber) {
                    $priceNotificationJobs[] = new SendPriceNotificationJob($subscriber, $bitfinexApiData['last_price']);
                }

                Bus::batch($priceNotificationJobs)->dispatch();

                return true;
        });
    }
}
