<?php

use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\RecentDonations;
use App\Filament\Widgets\RecentTransactions;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

describe('Dashboard', function () {
    it('can render dashboard page', function () {
        $this->get('/admin')
            ->assertOk();
    });

    it('can render stats overview widget', function () {
        expect(DashboardStatsOverview::canView())->toBeTrue();

        livewire(DashboardStatsOverview::class)
            ->assertOk();
    });

    it('cannot render stats overview widget for non-admin', function () {
        $user = User::factory()->staff()->create();
        $this->actingAs($user);

        expect(DashboardStatsOverview::canView())->toBeFalse();
    });

    it('can render recent donations widget', function () {
        livewire(RecentDonations::class)
            ->assertOk();
    });

    it('can render recent transactions widget', function () {
        livewire(RecentTransactions::class)
            ->assertOk();
    });
});
