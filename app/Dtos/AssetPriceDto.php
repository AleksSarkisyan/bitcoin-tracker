<?php

namespace App\Dtos;

use DateTime;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Data;

class AssetPriceDto extends Data
{
    public function __construct(
        public readonly string $symbol,
        public int|Optional $current_price,
        public readonly int $bid,
        public readonly int $ask,
        public readonly int $mts,
        public readonly int $percent_difference,
        public readonly DateTime $created_at = new DateTime(),
        public readonly DateTime $updated_at = new DateTime()
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            symbol: $data['symbol'],
            current_price: $data['current_price'],
            bid: $data['bid'],
            ask: $data['ask'],
            mts: $data['mts'],
            percent_difference: $data['percent_difference'],
            created_at: $data['created_at'],
            updated_at: $data['updated_at']
        );
    }
}
