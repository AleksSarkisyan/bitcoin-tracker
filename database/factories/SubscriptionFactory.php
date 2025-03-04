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
            // Generate a random 3-letter symbol (in uppercase)
            'symbol' => $this->faker->randomElement(['tBTCUSD', 'tBTCEUR']),
            // A random decimal number between 1 and 1000 with 8 decimal places precision
            'target_price' => $this->faker->numberBetween(80000, 85000),
            // Random percent change between 1 and 100
            'percent_change' => $this->faker->numberBetween(-10, 10),
            // Random current percent change between -10 and 10
            //'current_percent_change' => $this->faker->numberBetween(-10, 10),
            // Random time interval from a set of options (in hours)
            'time_interval' => $this->faker->randomElement(['1h', '6h', '24h']),
            // Optionally set a notification timestamp or leave null
            'percent_change_notified_on' => null,
            'target_price_notified_on' => null
        ];
    }
}
