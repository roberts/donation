<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Gate;

class SystemHealthWidget extends Widget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected string $view = 'filament.widgets.system-health-widget';

    public static function canView(): bool
    {
        return Gate::allows('viewPulse');
    }
}
