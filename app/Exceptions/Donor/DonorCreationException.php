<?php

declare(strict_types=1);

namespace App\Exceptions\Donor;

use Exception;

class DonorCreationException extends Exception
{
    public static function emailConflict(string $email): self
    {
        return new self("A user with the email {$email} already exists but is not linked to a donor record.");
    }

    public static function failed(string $message): self
    {
        return new self("Failed to create donor: {$message}");
    }
}
