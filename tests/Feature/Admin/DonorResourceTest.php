<?php

use App\Filament\Resources\Donors\Pages\CreateDonor;
use App\Filament\Resources\Donors\Pages\EditDonor;
use App\Filament\Resources\Donors\Pages\ListDonors;
use App\Models\Donor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

describe('Donor Resource', function () {
    it('can render index page', function () {
        livewire(ListDonors::class)
            ->assertOk();
    });

    it('can list donors', function () {
        $donors = Donor::factory()->count(5)->create();

        livewire(ListDonors::class)
            ->assertCanSeeTableRecords($donors);
    });

    it('can render create page', function () {
        livewire(CreateDonor::class)
            ->assertOk();
    });

    it('can create donor', function () {
        $newData = Donor::factory()->make();

        livewire(CreateDonor::class)
            ->fillForm([
                'first_name' => $newData->first_name,
                'last_name' => $newData->last_name,
                'email' => $newData->email,
                'phone' => $newData->phone,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Donor::class, [
            'email' => $newData->email,
        ]);
    });

    it('can render edit page', function () {
        $donor = Donor::factory()->create();

        livewire(EditDonor::class, ['record' => $donor->id])
            ->assertOk();
    });

    it('can update donor', function () {
        $donor = Donor::factory()->create();
        $newData = Donor::factory()->make();

        livewire(EditDonor::class, ['record' => $donor->id])
            ->fillForm([
                'first_name' => $newData->first_name,
                'last_name' => $newData->last_name,
                'email' => $newData->email,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($donor->refresh())
            ->first_name->toBe($newData->first_name)
            ->last_name->toBe($newData->last_name)
            ->email->toBe($newData->email);
    });

    it('can search donors by name', function () {
        $donor = Donor::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $otherDonor = Donor::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        livewire(ListDonors::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$donor])
            ->assertCanNotSeeTableRecords([$otherDonor]);
    });

    it('can search donors by email', function () {
        $donor = Donor::factory()->create([
            'email' => 'john@example.com',
        ]);
        $otherDonor = Donor::factory()->create([
            'email' => 'jane@example.com',
        ]);

        livewire(ListDonors::class)
            ->searchTable('john@example.com')
            ->assertCanSeeTableRecords([$donor])
            ->assertCanNotSeeTableRecords([$otherDonor]);
    });
});
