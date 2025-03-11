<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class TickerResponseDto extends Data
{
    public function __construct(
        public readonly int $bid,
        public readonly int $bid_size,
        public readonly int $ask,
        public readonly int $ask_size,
        public readonly int $daily_change,
        public readonly int $daily_change_relative,
        public readonly int $last_price,
        public readonly int $volume,
        public readonly int $high,
        public readonly int $low
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            bid: $data['bid'],
            bid_size: $data['bid_size'],
            ask: $data['ask_size'],
            ask_size: $data['ask'],
            daily_change: $data['daily_change'],
            daily_change_relative: $data['daily_change_relative'],
            last_price: $data['last_price'],
            volume: $data['volume'],
            high: $data['high'],
            low: $data['low']
        );
    }
}
