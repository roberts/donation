<?php

declare(strict_types=1);

namespace App\Exceptions\Donation;

use Exception;

class InvalidFilingYearException extends Exception
{
    public static function closed(int $year): self
    {
        return new self("The filing year {$year} is closed for new donations.");
    }

    public static function undetermined(): self
    {
        return new self('Could not determine a valid filing year.');
    }
}
