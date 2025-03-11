<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class UniquePairsDto extends Data
{
    public function __construct(
        public readonly string $time_interval,
        public readonly string $symbol
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            time_interval: $data['time_interval'],
            symbol: $data['symbol']
        );
    }
}
