<?php

namespace App\Dtos;

use DateTime;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class SubscriberDto extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $symbol,
        public readonly int|Optional $target_price,
        public readonly int $percent_change,
        public readonly string $time_interval,
        public readonly DateTime|null $percent_change_notified_on = null,
        public readonly DateTime|null $target_price_notified_on = null,
        public readonly DateTime $created_at = new DateTime(),
        public readonly DateTime $updated_at = new DateTime()
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            email: $data['email'],
            symbol: $data['symbol'],
            target_price: $data['target_price'],
            percent_change: $data['percent_change'],
            time_interval: $data['time_interval'],
            percent_change_notified_on: $data['percent_change_notified_on'],
            target_price_notified_on: $data['target_price_notified_on'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at']
        );
    }
}
