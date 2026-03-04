<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FilingStatus: string implements HasLabel
{
    case Single = 'single';
    case MarriedFilingSeparately = 'married_separately';
    case MarriedFilingJointly = 'married_jointly';

    public function getLabel(): string
    {
        return match ($this) {
            self::Single => 'Single',
            self::MarriedFilingSeparately => 'Married Filing Separately',
            self::MarriedFilingJointly => 'Married Filing Jointly',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
