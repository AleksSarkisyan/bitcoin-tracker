<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'symbol' => $this->faker->randomElement(['tBTCUSD', 'tBTCEUR']),
            'target_price' => $this->faker->numberBetween(80000, 85000),
            'percent_change' => $this->faker->numberBetween(-10, 10),
            'time_interval' => $this->faker->randomElement(['1h', '6h', '24h']),
            'percent_change_notified_on' => null,
            'target_price_notified_on' => null
        ];
    }
}
