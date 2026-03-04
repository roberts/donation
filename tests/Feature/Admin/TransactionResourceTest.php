<?php

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\ViewTransaction;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

describe('Transaction Resource', function () {
    it('can render index page', function () {
        livewire(ListTransactions::class)
            ->assertOk();
    });

    it('can list transactions', function () {
        $transactions = Transaction::factory()->count(5)->create();

        livewire(ListTransactions::class)
            ->assertCanSeeTableRecords($transactions);
    });

    it('can render view page', function () {
        $transaction = Transaction::factory()->create();

        livewire(ViewTransaction::class, ['record' => $transaction->id])
            ->assertOk();
    });

    it('displays correct transaction data on view page', function () {
        $transaction = Transaction::factory()->create([
            'amount' => 12345,
            'payment_intent_id' => 'pi_test_123',
        ]);

        livewire(ViewTransaction::class, ['record' => $transaction->id])
            ->assertFormSet([
                'amount' => '123.45',
                'payment_intent_id' => 'pi_test_123',
            ]);
    });

    it('can search transactions by payment intent id', function () {
        $transaction = Transaction::factory()->create([
            'payment_intent_id' => 'pi_search_me',
        ]);
        $otherTransaction = Transaction::factory()->create([
            'payment_intent_id' => 'pi_dont_find_me',
        ]);

        livewire(ListTransactions::class)
            ->searchTable('pi_search_me')
            ->assertCanSeeTableRecords([$transaction])
            ->assertCanNotSeeTableRecords([$otherTransaction]);
    });

    it('sorts by created_at descending by default', function () {
        $oldTransaction = Transaction::factory()->create([
            'created_at' => now()->subDays(5),
        ]);
        $newTransaction = Transaction::factory()->create([
            'created_at' => now(),
        ]);

        livewire(ListTransactions::class)
            ->assertCanSeeTableRecords([$newTransaction, $oldTransaction], inOrder: true);
    });
});
