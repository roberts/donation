<?php

namespace App\Contracts;

use App\Data\PaymentData;
use App\Data\PaymentResult;

interface PaymentGateway
{
    public function charge(PaymentData $data): PaymentResult;
}
