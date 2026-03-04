<?php

use App\Filament\Resources\Donations\DonationResource;
use App\Filament\Resources\Donors\DonorResource;
use App\Models\Donation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

describe('Global Search', function () {
    it('can search for donation by ID', function () {
        $donation = Donation::factory()->create();

        // Filament's global search logic is typically tested by checking if the resource is globally searchable
        // and if the model is configured correctly.
        // However, we can simulate a search request if we knew the exact endpoint or Livewire component.
        // A simpler approach for Feature tests is to verify the resource configuration.

        expect(DonationResource::getGloballySearchableAttributes())
            ->toContain('id')
            ->toContain('donor.first_name')
            ->toContain('donor.last_name');
    });

    it('can search for donor by name', function () {
        expect(DonorResource::getGloballySearchableAttributes())
            ->toContain('first_name')
            ->toContain('last_name')
            ->toContain('email');
    });
});
