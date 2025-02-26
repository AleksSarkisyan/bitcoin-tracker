<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{

    /**
     * Create a new subscription.
     */
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    /**
     * Update a subscription by ID.
     */
    public function update(int $id, array $data): bool
    {
        return Subscription::where('id', $id)->update($data);
    }

    public function checkIfExists(array $data): bool | Collection
    {
        return Subscription::where($data)->get();
    }

    public function getPriceSubscribers(int $currentPrice): null | Collection | Subscription | Builder
    {
        $query = Subscription::where('target_price', '<', $currentPrice)
            ->whereNull('target_price_notified_on');

        $subscriptionsCount = $query->count();

        /** Limits the number of emails to one per symbol and email per job run if a user has created multiple price alerts and current asset price is above the alert price. */
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

