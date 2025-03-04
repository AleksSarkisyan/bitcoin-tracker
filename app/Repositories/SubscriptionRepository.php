<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Support\Collection;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return Subscription::where('id', $id)->update($data);
    }

    public function checkIfExists(array $data): bool | Collection
    {
        return Subscription::where($data)->get();
    }

    /** Potentially add some adequate limit that will allow handling as many records as the job duration allows. */
    public function getPriceSubscribers(int $currentPrice, $symbol): mixed
    {
        $query = Subscription::where('target_price', '<', $currentPrice)
            ->whereNull('target_price_notified_on');

        $subscriptionsCount = $query->count();

        if ($subscriptionsCount > 1) {
            $subQuery = Subscription::selectRaw('MIN(id)')
                ->where('target_price', '<', $currentPrice)
                ->whereNull('target_price_notified_on')
                ->groupBy('email', 'symbol');

            $query = Subscription::where('target_price', '<', $currentPrice)
                ->whereNull('target_price_notified_on')
                ->whereIn('id', $subQuery);
        }

        return $query;
    }

    public function getPercentageSubscribers(array $times): mixed
    {
        $chunkSize = 100;
        Subscription::whereNull('percent_change_notified_on')
            ->whereIn('time_interval', $times)
            ->chunk($chunkSize, function ($chunk) use (&$grouped) {
                foreach ($chunk as $subscription) {
                    $symbol = $subscription->symbol;
                    $interval = $subscription->time_interval;

                    if (!isset($grouped[$symbol])) {
                        $grouped[$symbol] = [];
                    }

                    $grouped[$symbol][$interval][] = $subscription;
                }
            });

            $groupedArray = $grouped;

            return collect($groupedArray);
    }
}

