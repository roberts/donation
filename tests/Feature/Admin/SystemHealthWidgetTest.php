<?php

use App\Filament\Widgets\SystemHealthWidget;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

describe('System Health Widget', function () {
    it('can render widget for admin', function () {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        livewire(SystemHealthWidget::class)
            ->assertOk()
            ->assertSee('System Health');
    });

    it('cannot render widget for staff', function () {
        $user = User::factory()->staff()->create();
        $this->actingAs($user);

        expect(SystemHealthWidget::canView())->toBeFalse();
    });

    it('cannot render widget for donor', function () {
        $user = User::factory()->donor()->create();
        $this->actingAs($user);

        expect(SystemHealthWidget::canView())->toBeFalse();
    });
});
