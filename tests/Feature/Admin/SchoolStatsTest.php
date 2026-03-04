<?php

use App\Filament\Resources\Schools\Pages\ListSchools;
use App\Models\Donation;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
});

describe('School Stats', function () {
    it('shows correct donation count in table', function () {
        $school = School::factory()->create(['name' => 'School A']);

        // Create 3 donations for this school
        Donation::factory()->count(3)->create([
            'school_id' => $school->id,
        ]);

        // Create another school with 1 donation
        $otherSchool = School::factory()->create(['name' => 'School B']);
        Donation::factory()->create([
            'school_id' => $otherSchool->id,
        ]);

        Livewire::test(ListSchools::class)
            ->assertSee('3') // Check for the count 3
            ->assertSee('1'); // Check for the count 1
    });
});
