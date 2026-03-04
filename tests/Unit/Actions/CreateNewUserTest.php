<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

describe('CreateNewUser Action', function () {
    it('creates a user with valid input', function () {
        $action = new CreateNewUser;
        $input = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $user = $action->create($input);

        expect($user)->toBeInstanceOf(User::class)
            ->and($user->name)->toBe('Test User')
            ->and($user->email)->toBe('test@example.com');
    });

    it('validates required fields', function () {
        $action = new CreateNewUser;
        $input = [];

        expect(fn () => $action->create($input))->toThrow(ValidationException::class);
    });

    it('validates password confirmation', function () {
        $action = new CreateNewUser;
        $input = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
        ];

        expect(fn () => $action->create($input))->toThrow(ValidationException::class);
    });

    it('validates unique email', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        $action = new CreateNewUser;
        $input = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        expect(fn () => $action->create($input))->toThrow(ValidationException::class);
    });
});
