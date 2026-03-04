<?php

declare(strict_types=1);

arch('globals')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toUse('App\Models')
    ->ignoring('App\Http\Controllers\Auth') // Auth often uses models directly
    ->toOnlyUse([
        'Illuminate\Http\Request',
        'Illuminate\Support\Facades',
        'App\Actions',
        'App\Data',
        'App\Http\Requests',
        'Inertia\Inertia',
    ]);

arch('models')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->toOnlyBeUsedIn([
        'App\Actions',
        'App\Data',
        'App\Repositories',
        'App\Policies',
        'App\Observers',
        'App\Listeners',
        'App\Jobs',
        'App\Mail',
        'App\Notifications',
        'Database\Factories',
        'Database\Seeders',
        'Tests',
    ]);

arch('actions')
    ->expect('App\Actions')
    ->toHaveMethod('execute');

arch('enums')
    ->expect('App\Enums')
    ->toBeEnums();

arch('value objects')
    ->expect('App\Data')
    ->toBeClasses()
    ->toUseNothing(); // Should be simple DTOs
