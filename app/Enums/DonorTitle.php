<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DonorTitle: string implements HasLabel
{
    case Dr = 'Dr.';
    case Mr = 'Mr.';
    case Mrs = 'Mrs.';
    case Ms = 'Ms.';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
