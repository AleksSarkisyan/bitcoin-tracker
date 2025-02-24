<?php

namespace App\Traits;

use App\Repositories\SubscriptionRepositoryInterface;

trait SubscriptionTrait
{
    protected function subscriptionRepository()
    {
        return app(SubscriptionRepositoryInterface::class);
    }

    public function createPriceSubscription($data)
    {
        $exists = $this->subscriptionRepository()->checkIfExists($data);
        
        if (!$exists->count()) {
            $subs = $this->subscriptionRepository()->create($data);
            
            return true;
        }

        return false;
    }
}
