<?php

use App\Models\Donation;
use App\Models\Donor;
use App\Models\Note;
use App\Models\School;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Notes', function () {
    it('allows a user to have a donor profile', function () {
        $user = User::factory()->create();
        $donor = Donor::factory()->create(['user_id' => $user->id]);

        expect($user->donor)->toBeInstanceOf(Donor::class)
            ->and($user->donor->id)->toBe($donor->id)
            ->and($donor->user)->toBeInstanceOf(User::class)
            ->and($donor->user->id)->toBe($user->id);
    });

    it('allows a user to create notes', function () {
        $user = User::factory()->create();
        $note = Note::factory()->create(['creator_id' => $user->id]);

        expect($user->notes)->toHaveCount(1)
            ->and($user->notes->first()->id)->toBe($note->id)
            ->and($note->creator)->toBeInstanceOf(User::class)
            ->and($note->creator->id)->toBe($user->id);
    });

    it('can be attached to a donor', function () {
        $donor = Donor::factory()->create();
        $note = Note::factory()->create([
            'notable_type' => Donor::class,
            'notable_id' => $donor->id,
        ]);

        expect($donor->notes)->toHaveCount(1)
            ->and($donor->notes->first()->id)->toBe($note->id)
            ->and($note->notable)->toBeInstanceOf(Donor::class)
            ->and($note->notable->id)->toBe($donor->id);
    });

    it('can be attached to a donation', function () {
        $donation = Donation::factory()->create();
        $note = Note::factory()->create([
            'notable_type' => Donation::class,
            'notable_id' => $donation->id,
        ]);

        expect($donation->notes)->toHaveCount(1)
            ->and($donation->notes->first()->id)->toBe($note->id)
            ->and($note->notable)->toBeInstanceOf(Donation::class)
            ->and($note->notable->id)->toBe($donation->id);
    });

    it('can be attached to a school', function () {
        $school = School::factory()->create();
        $note = Note::factory()->create([
            'notable_type' => School::class,
            'notable_id' => $school->id,
        ]);

        expect($school->notes)->toHaveCount(1)
            ->and($school->notes->first()->id)->toBe($note->id)
            ->and($note->notable)->toBeInstanceOf(School::class)
            ->and($note->notable->id)->toBe($school->id);
    });

    it('can be attached to a transaction', function () {
        $transaction = Transaction::factory()->create();
        $note = Note::factory()->create([
            'notable_type' => Transaction::class,
            'notable_id' => $transaction->id,
        ]);

        expect($transaction->notes)->toHaveCount(1)
            ->and($transaction->notes->first()->id)->toBe($note->id)
            ->and($note->notable)->toBeInstanceOf(Transaction::class)
            ->and($note->notable->id)->toBe($transaction->id);
    });
});
