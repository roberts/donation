<?php

namespace App\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public static function declined(string $message): self
    {
        return new self("Payment declined: {$message}");
    }

    public static function providerError(string $message): self
    {
        return new self("Payment provider error: {$message}");
    }
}
