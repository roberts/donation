<?php

declare(strict_types=1);

namespace App\Exceptions\Donation;

use Exception;

class ReceiptGenerationException extends Exception
{
    public static function failed(string $message): self
    {
        return new self("Failed to generate donation receipt: {$message}");
    }
}
