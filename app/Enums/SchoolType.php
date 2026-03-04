<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SchoolType: string implements HasColor, HasLabel
{
    case Private = 'private';
    case Public = 'public';
    case Charter = 'charter';

    public function getLabel(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::Public => 'Public',
            self::Charter => 'Charter',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Private => 'danger',
            self::Public => 'success',
            self::Charter => 'info',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
