<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;

    public function update(int $id, array $data): bool;

    public function checkIfExists(array $data): bool | Collection;

    public function getPriceSubscribers(int $currentPrice): null | Collection | Subscription | Builder;

    public function getPercentageSubscribers(array $times): mixed;

}
