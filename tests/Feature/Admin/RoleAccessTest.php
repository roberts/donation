<?php

use App\Filament\Resources\Donations\DonationResource;
use App\Filament\Resources\Donors\DonorResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Note;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(RolesAndPermissionsSeeder::class);
});

describe('Panel Access', function () {
    it('allows users with correct roles to access filament', function ($role) {
        $user = User::factory()->{$role}()->create();

        actingAs($user)
            ->get('/admin')
            ->assertSuccessful();
    })->with(['admin', 'staff', 'donor']);

    it('prevents users without roles from accessing filament', function () {
        $user = User::factory()->create();

        actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    });
});

describe('Resource Scoping', function () {
    it('restricts donors to see only their own donor record', function () {
        $user = User::factory()->donor()->create();

        Donor::factory()->create(['user_id' => $user->id, 'last_name' => 'MyUniqueName']);
        Donor::factory()->create(['last_name' => 'OtherUniqueName']);

        actingAs($user)
            ->get(DonorResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('MyUniqueName')
            ->assertDontSee('OtherUniqueName');
    });

    it('restricts donors to see only their own donations', function () {
        $user = User::factory()->donor()->create();

        $myDonor = Donor::factory()->create(['user_id' => $user->id]);
        Donation::factory()->create(['donor_id' => $myDonor->id, 'amount' => 12345]);

        $otherDonor = Donor::factory()->create();
        Donation::factory()->create(['donor_id' => $otherDonor->id, 'amount' => 67890]);

        actingAs($user)
            ->get(DonationResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('123.45')
            ->assertDontSee('678.90');
    });

    it('restricts donors to see only their own transactions', function () {
        $user = User::factory()->donor()->create();

        $myDonor = Donor::factory()->create(['user_id' => $user->id]);
        $myDonation = Donation::factory()->create(['donor_id' => $myDonor->id]);
        Transaction::factory()->create(['donation_id' => $myDonation->id, 'amount' => 11111]);

        $otherDonor = Donor::factory()->create();
        $otherDonation = Donation::factory()->create(['donor_id' => $otherDonor->id]);
        Transaction::factory()->create(['donation_id' => $otherDonation->id, 'amount' => 22222]);

        actingAs($user)
            ->get(TransactionResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('111.11')
            ->assertDontSee('222.22');
    });

    it('allows admins to see all donors', function () {
        $user = User::factory()->admin()->create();

        Donor::factory()->create(['last_name' => 'DonorOne']);
        Donor::factory()->create(['last_name' => 'DonorTwo']);

        actingAs($user)
            ->get(DonorResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('DonorOne')
            ->assertSee('DonorTwo');
    });
});

describe('Policy Enforcement', function () {
    it('enforces note update policy', function () {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();
        $donor = User::factory()->donor()->create();

        $note = Note::factory()->create(['creator_id' => $admin->id]);

        // Admin can edit own note
        expect($admin->can('update', $note))->toBeTrue();

        // Staff cannot edit admin's note (policy restriction)
        expect($staff->can('update', $note))->toBeFalse();

        // Donor cannot edit admin's note
        expect($donor->can('update', $note))->toBeFalse();

        $donorNote = Note::factory()->create(['creator_id' => $donor->id]);
        // Donor cannot edit own note (missing permission in seeder)
        expect($donor->can('update', $donorNote))->toBeFalse();
    });
});
