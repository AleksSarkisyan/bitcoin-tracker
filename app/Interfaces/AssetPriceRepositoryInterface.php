<?php

namespace App\Interfaces;

use App\Models\AssetPrice;
use Illuminate\Support\Collection;

interface AssetPriceRepositoryInterface
{
    public function create(array $data): AssetPrice;

    public function getPriceBySymbolsAndHours(array $symbols, array $times): array;

    public function getPriceBySymbols(array $symbol): Collection;
}
