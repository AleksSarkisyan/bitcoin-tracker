<?php

namespace App\Dtos;

use Spatie\LaravelData\Data;

class TickerHistoryParamsDto extends Data
{
    public function __construct(
        public readonly string $symbols,
        public readonly int $limit,
        public readonly int $end
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            symbols: $data['symbols'],
            limit: $data['limit'],
            end: $data['end']
        );
    }
}
