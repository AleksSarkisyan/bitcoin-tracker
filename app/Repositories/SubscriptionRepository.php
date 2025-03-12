<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Support\Collection;
use App\Interfaces\SubscriptionRepositoryInterface;
use App\Dtos\UniquePairsDto;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public int $chunkSize;

    public function __construct()
    {
        $this->chunkSize = 100;
    }

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

    /** Potentially add some adequate limit that will allow handling as many records as a single cron can handle. */
    public function getPriceSubscribers(int $currentPrice, string $symbol): Builder
    {
        $query = Subscription::where('target_price', '<', $currentPrice)
            ->where('symbol', $symbol)
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

        // dd($query->toSql(), $query->getBindings());
        return $query;
    }

    public function getUniqueSymbolHourPairs(): Collection
    {
        $uniquePairs = Subscription::select('time_interval', 'symbol')
            ->whereNotNull('time_interval')
            ->distinct()
            ->get();

        return UniquePairsDto::collect($uniquePairs, Collection::class);
    }

    public function getPercentageSubscribers(array $times): Builder
    {
        $query = Subscription::whereNull('percent_change_notified_on')
            ->whereIn('time_interval', $times);

            return $query;
    }
}

