<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Support\Collection;

interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;

    public function update(int $id, array $data): bool;

    public function checkIfExists(array $data): bool | Collection;

    public function getPriceSubscribers(int $currentPrice, $symbol): mixed;

    public function getPercentageSubscribers(array $times): mixed;
}
