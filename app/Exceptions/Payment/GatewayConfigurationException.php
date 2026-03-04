<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use Exception;

class GatewayConfigurationException extends Exception
{
    public static function missingApiKey(): self
    {
        return new self('The payment gateway API key is missing.');
    }

    public static function invalidConfiguration(string $message): self
    {
        return new self("Payment gateway configuration error: {$message}");
    }
}
