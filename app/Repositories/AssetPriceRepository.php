<?php

namespace App\Repositories;

use App\Models\AssetPrice;
use Illuminate\Support\Collection;
use App\Interfaces\AssetPriceRepositoryInterface;

class AssetPriceRepository implements AssetPriceRepositoryInterface
{
    public function create(array $data): AssetPrice
    {
        return AssetPrice::create($data);
    }

    public function getPriceBySymbolsAndHours(array $symbols, array $times): array
    {
        $limit = count($symbols) * count($times);
        $result = AssetPrice::whereIn('symbol', $symbols)
            ->whereIn('time_interval', $times)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->groupBy('symbol')
            ->map(function ($items) {
                return $items->groupBy('time_interval');
            })
            ->toArray();

            // dd($query->toSql(), $query->getBindings());

            return $result;
    }

    public function getPriceBySymbols(array $symbols): Collection
    {
        return AssetPrice::whereIn('symbol', $symbols)
            ->orderBy('created_at', 'desc')
            ->limit(count($symbols))
            ->get();
    }
}

