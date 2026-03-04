<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class PaymentData extends Data
{
    public function __construct(
        public int $amount,
        public string $token,
        public array $metadata = [],
        public string $description = '',
        public string $currency = 'usd',
    ) {}
}
