<?php

use App\Enums\SchoolType;
use App\Filament\Resources\Schools\Pages\CreateSchool;
use App\Filament\Resources\Schools\Pages\EditSchool;
use App\Filament\Resources\Schools\Pages\ListSchools;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

describe('School Resource', function () {
    it('can render index page', function () {
        livewire(ListSchools::class)
            ->assertOk();
    });

    it('can list schools', function () {
        $schools = School::factory()->count(5)->create();

        livewire(ListSchools::class)
            ->assertCanSeeTableRecords($schools);
    });

    it('can render create page', function () {
        livewire(CreateSchool::class)
            ->assertOk();
    });

    it('can create school', function () {
        $schoolData = [
            'name' => 'Test Private School',
            'type' => SchoolType::Private->value,
            'ibe_id' => 12345,
        ];

        livewire(CreateSchool::class)
            ->fillForm($schoolData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('schools', [
            'name' => 'Test Private School',
            'type' => SchoolType::Private->value,
        ]);
    });

    it('can validate required fields on create', function () {
        livewire(CreateSchool::class)
            ->fillForm([
                'name' => '',
                'type' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'type' => 'required']);
    });

    it('can render edit page', function () {
        $school = School::factory()->create();

        livewire(EditSchool::class, ['record' => $school->id])
            ->assertOk();
    });

    it('can retrieve school data for editing', function () {
        $school = School::factory()->create([
            'name' => 'Existing School',
            'type' => SchoolType::Public,
        ]);

        livewire(EditSchool::class, ['record' => $school->id])
            ->assertFormSet([
                'name' => 'Existing School',
                'type' => SchoolType::Public,
            ]);
    });

    it('can update school', function () {
        $school = School::factory()->create();

        livewire(EditSchool::class, ['record' => $school->id])
            ->fillForm([
                'name' => 'Updated School Name',
                'type' => SchoolType::Charter->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $school->refresh();

        expect($school->name)->toBe('Updated School Name')
            ->and($school->type)->toBe(SchoolType::Charter);
    });

    it('can delete school', function () {
        $school = School::factory()->create();

        livewire(EditSchool::class, ['record' => $school->id])
            ->callAction('delete');

        $this->assertSoftDeleted($school);
    });

    it('can search schools by name', function () {
        $matchingSchool = School::factory()->create(['name' => 'Desert Mountain School']);
        $nonMatchingSchool = School::factory()->create(['name' => 'Ocean View Academy']);

        livewire(ListSchools::class)
            ->searchTable('Desert')
            ->assertCanSeeTableRecords([$matchingSchool])
            ->assertCanNotSeeTableRecords([$nonMatchingSchool]);
    });

    it('can filter schools by type', function () {
        $privateSchool = School::factory()->create(['type' => SchoolType::Private]);
        $publicSchool = School::factory()->create(['type' => SchoolType::Public]);

        livewire(ListSchools::class)
            ->filterTable('type', SchoolType::Private->value)
            ->assertCanSeeTableRecords([$privateSchool])
            ->assertCanNotSeeTableRecords([$publicSchool]);
    });
});
