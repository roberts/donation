<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Card = 'card';
    case Check = 'check';

    public function getLabel(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::Check => 'Check',
        };
    }
}
