<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('User Model', function () {
    it('allows admin to access panel', function () {
        $user = User::factory()->admin()->create();
        $panel = Mockery::mock(Panel::class);

        expect($user->canAccessPanel($panel))->toBeTrue();
    });

    it('allows staff to access panel', function () {
        $user = User::factory()->staff()->create();
        $panel = Mockery::mock(Panel::class);

        expect($user->canAccessPanel($panel))->toBeTrue();
    });

    it('allows donor to access panel', function () {
        $user = User::factory()->donor()->create();
        $panel = Mockery::mock(Panel::class);

        expect($user->canAccessPanel($panel))->toBeTrue();
    });

    it('denies access to user without role', function () {
        $user = User::factory()->create();
        $panel = Mockery::mock(Panel::class);

        expect($user->canAccessPanel($panel))->toBeFalse();
    });
});
