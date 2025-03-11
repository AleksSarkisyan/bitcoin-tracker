<?php

namespace App\Services;

use App\Interfaces\SubscriptionRepositoryInterface;
use App\Jobs\SendPercentageNotificationJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
Use Carbon\Carbon;
use App\Jobs\SendPriceNotificationJob;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\AssetPriceRepositoryInterface;
use App\Traits\Helper;
use App\Enums\Jobs;

class NotificationService extends ServiceProvider
{
    use Helper;

    public $subscriptionRepository;
    public $assetPriceRepository;
    public int $chunkSize;
    public $percentageNotificationJob = Jobs::SEND_PERCENTAGE_NOTIFICATION->value;
    public $priceNotificationJob = Jobs::SEND_PRICE_NOTIFICATION->value;
    public array $availableSymbols;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        AssetPriceRepositoryInterface $assetPriceRepository
    )
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->assetPriceRepository = $assetPriceRepository;
        $this->availableSymbols = config('bitfinex.availableSymbols');
        $this->chunkSize = 100;
    }

    public function processPercentageSubscriptions(): string
    {
        $availableIntervals = config('bitfinex.availableIntervals');

        /** @var \App\Models\AssetPrice $currentPrices */
        $currentPrices = $this->assetPriceRepository->getPriceBySymbolsAndHours($this->availableSymbols, $availableIntervals);

        if (empty($currentPrices)) {
            return $this->currentPricesError($currentPrices);
        }

        $queryStart = microtime(true);
        $percentageSubscribers = $this->subscriptionRepository->getPercentageSubscribers($availableIntervals);

        $duration = microtime(true) - $queryStart;
        Log::info($this->percentageNotificationJob . ' getPercentageSubscribers query executed in ' . $duration . ' seconds', []);

        $percentageSubscribers->orderBy('id')->chunkById($this->chunkSize, function ($percentageSubscribers) use ($currentPrices) {
            Log::info($this->percentageNotificationJob .' - Begin job processing for ' . $percentageSubscribers->count() . ' records');

            $percentageJobs = [];

            /** @var \App\Models\Subscription $subscriber */
            foreach ($percentageSubscribers as $subscriber) {
                if ($this->isUserSubscribed($currentPrices, $subscriber)) {
                    $historyPercentageDifference = 0;

                    if (isset($currentPrices[$subscriber->symbol]) && isset($currentPrices[$subscriber->symbol][$subscriber->time_interval])) {
                        $historyPercentageDifference = $currentPrices[$subscriber->symbol][$subscriber->time_interval][0]['percent_difference'];
                    }

                    $userPercentageChange = $subscriber->percent_change;

                    $hasAdditionalLogging = config('bitfinex.v2.tickersHistory.additionalLogging');

                    /** Enable for debugging */
                    if ($hasAdditionalLogging) {
                        Log::info($this->percentageNotificationJob . ' History data for ' . $subscriber->symbol .' and interval ' . $subscriber->time_interval . ' - ' . $historyPercentageDifference);
                        Log::info($this->percentageNotificationJob . ' User id ' . $subscriber->id . ' percentage ' . $userPercentageChange);
                    }

                    if ($this->hasPriceDropped($userPercentageChange, $historyPercentageDifference) ||
                        ($this->hasPriceJumped($userPercentageChange, $historyPercentageDifference))) {
                        Log::info($this->percentageNotificationJob . ' Condition met for user id -' . $subscriber->id);
                        $percentageJobs[] = new SendPercentageNotificationJob($subscriber, $historyPercentageDifference);
                    }
                }
            }

            Helper::batchJobs($percentageJobs);
        });

        return 'PercentageSubscriptionCommand - END!';
    }

    public function calculatePercentageDifference(int $firstNumber, int $secondNumber): int
    {
        return (($firstNumber - $secondNumber) / $secondNumber) * 100;
    }

    public function getMsTimestamp(int $hours): int
    {
        return Carbon::now()->subHour($hours)->toArray()['timestamp'] * 1000;
    }

    public function isUserSubscribed($hourPercentDiffs, $subscriber): bool
    {
        return (isset($hourPercentDiffs[$subscriber->symbol]) &&
            isset($hourPercentDiffs[$subscriber->symbol][$subscriber->time_interval]));
    }

    public function hasPriceDropped(int $userPercentageChange, int $historyPercentageChange): bool
    {
        return $userPercentageChange < 0 && $historyPercentageChange <= $userPercentageChange;
    }

    public function hasPriceJumped(int $userPercentageChange, int $historyPercentageChange): bool
    {
        return $userPercentageChange > 0 && $historyPercentageChange >= $userPercentageChange;
    }

    public function processPriceSubscriptions(): string
    {
        /** @var \Illuminate\Database\Eloquent\Builder $subscribers */
        $subscribers = [];
        $assetPrices = $this->assetPriceRepository->getPriceBySymbols($this->availableSymbols);

        if (empty($assetPrices)) {
           return $this->currentPricesError($assetPrices);
        }

        foreach ($assetPrices as $asset) {
            $queryStart = microtime(true);
            $subscribers = $this->subscriptionRepository->getPriceSubscribers($asset->current_price, $asset->symbol);

            $subscribers->orderBy('id')
                ->chunkById($this->chunkSize, function ($subscribers) use ($asset) {
                    Log::info($this->priceNotificationJob . ' Begin job processing for ' . $subscribers->count() . ' records');

                    foreach ($subscribers as $subscriber) {
                        $priceNotificationJobs[] = new SendPriceNotificationJob($subscriber, $asset['current_price']);
                    }

                    Helper::batchJobs($priceNotificationJobs);
            });

            $duration = microtime(true) - $queryStart;
            Log::info($this->priceNotificationJob . ' getPriceSubscribers query executed in ' . $duration . ' seconds', []);
        }

        return $this->priceNotificationJob . ' - END!';;
    }

    public function currentPricesError(): string
    {
        /** Any logic to handle the case when there are no asset prices. Additionally, any logic to handle stale data could be here. */
        $message = $this->percentageNotificationJob . ' No current prices found!';
        Log::info($message);
        return $message;
    }
}
