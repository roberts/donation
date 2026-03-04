<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class PaymentResult extends Data
{
    public function __construct(
        public string $id,
        public int $amount,
        public string $status,
        public bool $livemode,
        public mixed $originalResponse = null,
    ) {}
}
