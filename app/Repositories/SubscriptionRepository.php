<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * Get all subscriptions.
     */
    public function getAll(): Collection
    {
        return Subscription::all();
    }

    /**
     * Find a subscription by its ID.
     */
    public function findById(int $id): ?Subscription
    {
        return Subscription::find($id);
    }

    /**
     * Find all subscriptions by user ID.
     */
    public function findByUserId(int $userId): Collection
    {
        return Subscription::where('user_id', $userId)->get();
    }

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

    /**
     * Delete a subscription by ID.
     */
    public function delete(int $id): bool
    {
        return Subscription::destroy($id) > 0;
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

        /** Limits the number of emails to one if a user has created multiple price alerts and current asset price is above/below the alert price. */
        if ($subscriptionsCount > 1) {
            $query->whereRaw('id IN (SELECT MIN(id) FROM subscriptions GROUP BY email)');
        }

        return $query;
    }
}
