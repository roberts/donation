<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget;

class WelcomeWidget extends AccountWidget
{
    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];
}
