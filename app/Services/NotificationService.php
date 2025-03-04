<?php

namespace App\Services;

use App\Repositories\SubscriptionRepositoryInterface;
use App\Facades\Bitfinex;
use App\Jobs\SendPercentageNotificationJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
Use Carbon\Carbon;
use App\Jobs\SendPriceNotificationJob;

class NotificationService
{
    protected $subscriptionRepository;
    public $availableIntervals;
    public $availableSymbols;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->availableSymbols = config('bitfinex.availableSymbols');
        $this->availableIntervals = config('bitfinex.availableIntervals');
    }

    public function processPercentageSubscriptions()
    {
        $queryStart = microtime(true);
        $percentageSubscribers = $this->subscriptionRepository->getPercentageSubscribers($this->availableIntervals);

        $duration = microtime(true) - $queryStart;
        Log::info('getPercentageSubscribers query executed in ' . $duration . ' seconds', []);

        $symbols = $this->getSymbols($percentageSubscribers);

        $tickerHistoryParams = [
            'symbols' => $symbols,
            'limit' => 1
        ];

        $hourPercentDiffs = [];
        $usersToNotify = [];
        foreach ($percentageSubscribers as $symbol => $hours) {
            $tickerParams = [
                'query' => $symbol
            ];

            Log::info('PercentageSubscriptionCommand - Calling Bitfinex ticker endpoint with ' . $symbol);
            $tickerResponse = Bitfinex::get($endpointType = 'ticker', $tickerParams);

            foreach ($hours as $hour => $users) {
                $numberOfHours = intval($hour);
                $timestampMs = $this->getMsTimestamp($numberOfHours);
                $tickerHistoryParams['end'] = $timestampMs;

                $params = [
                    'query' => '?' .http_build_query($tickerHistoryParams)
                ];

                $endpointType = 'tickersHistory';

                Log::info('PercentageSubscriptionCommand - Calling Bitfinex tickerHistory endpoint with ' . $symbols);
                $tickerHistory = Bitfinex::get($endpointType, $params);
                $tickerHistoryResponse = Bitfinex::formatResponse($endpointType, $tickerHistory[0]);

                Log::info('PercentageSubscriptionCommand - Calculating % difference for selected symbols and hours.');
                $hourPercentDiffs[$symbol][$hour] = $this->calculatePercentageDifference($tickerHistoryResponse['bid'], $tickerResponse['bid']);
                $usersToNotify = array_merge($usersToNotify, $users);
            }
        }

        $percentageJobs = [];
        foreach ($usersToNotify as $user) {
            if ($this->isUserSubscribed($hourPercentDiffs, $user)) {
                $historyPercentageChange = $hourPercentDiffs[$user->symbol][$user->time_interval];
                $userPercentageChange = $user->percent_change;

                Log::info('PercentageSubscriptionCommand - History data for ' . $user->symbol .' and interval ' . $user->time_interval . ' - ' . $historyPercentageChange);
                Log::info('PercentageSubscriptionCommand - User id ' . $user->id . ' percentage ' . $userPercentageChange);

                if ($this->hasPriceDropped($userPercentageChange, $historyPercentageChange) ||
                    ($this->hasPriceJumped($userPercentageChange, $historyPercentageChange))) {
                    Log::info('PercentageSubscriptionCommand - Condition met for user id -' . $user->id);
                    $percentageJobs[] = new SendPercentageNotificationJob($user, $historyPercentageChange);
                }
            }
        }

        if (count($percentageJobs)) {
            Log::info('PercentageSubscriptionCommand - Batching ' . count($percentageJobs) . ' records.');
            Bus::batch($percentageJobs)->dispatch();
            return 'PercentageSubscriptionCommand - END!';
        }

        Log::info('PercentageSubscriptionCommand - Nothing to batch.');
        return 'PercentageSubscriptionCommand - END!';
    }

    public function calculatePercentageDifference($firstNumber, $secondNumber)
    {
        return (($firstNumber - $secondNumber) / $secondNumber) * 100;
    }

    public function getSymbols($percentageSubscribers)
    {
        return implode(
            ',',
            array_keys($percentageSubscribers->toArray())
        );
    }

    public function getMsTimestamp($hours)
    {
        return Carbon::now()->subHour($hours)->toArray()['timestamp'] * 1000;
    }

    public function isUserSubscribed($hourPercentDiffs, $user)
    {
        return (isset($hourPercentDiffs[$user->symbol]) &&
            isset($hourPercentDiffs[$user->symbol][$user->time_interval]));
    }

    public function hasPriceDropped($userPercentageChange, $historyPercentageChange)
    {
        return $userPercentageChange < 0 && $historyPercentageChange <= $userPercentageChange;
    }

    public function hasPriceJumped($userPercentageChange, $historyPercentageChange)
    {
        return $userPercentageChange > 0 && $historyPercentageChange >= $userPercentageChange;
    }

    public function processPriceSubscriptions()
    {
        $availableSymbols = config('bitfinex.availableSymbols');

        $subscribers = [];
        foreach ($availableSymbols as $symbol) {
            $params = [
                'query' => $symbol
            ];

            $bitfinexApiData[$symbol] = Bitfinex::get($endpointType = 'ticker', $params);

            if (!$bitfinexApiData[$symbol] || isset($bitfinexApiData[$symbol]['error'])) {
                return false;
            }

            $currentPrice[$symbol] = intval($bitfinexApiData[$symbol]['last_price']);

            $queryStart[$symbol] = microtime(true);
            $subscribers[$symbol] = $this->subscriptionRepository->getPriceSubscribers($currentPrice[$symbol], $symbol);
            $chunkSize = 100;

            $subscribers[$symbol]->orderBy('id')
                ->chunkById($chunkSize, function ($subscribers) use ($bitfinexApiData, $symbol) {
                    Log::info('PriceSubscriptionCommand - Begin job processing for ' . $subscribers->count() . ' records');

                    foreach ($subscribers as $subscriber) {
                        $priceNotificationJobs[$symbol][] = new SendPriceNotificationJob($subscriber, $bitfinexApiData[$symbol]['last_price']);
                    }

                    Bus::batch($priceNotificationJobs[$symbol])->dispatch();
            });

            $duration = microtime(true) - $queryStart[$symbol];
            Log::info('getPriceSubscribers query executed in ' . $duration . ' seconds', [$symbol]);
        }

        return true;
    }
}
