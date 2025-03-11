<?php

namespace App\Interfaces;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;

    public function update(int $id, array $data): bool;

    public function checkIfExists(array $data): bool | Collection;

    public function getPriceSubscribers(int $currentPrice, string $symbol): Builder;

    public function getUniqueSymbolHourPairs(): Collection;

    public function getPercentageSubscribers(array $times): Builder;

}
