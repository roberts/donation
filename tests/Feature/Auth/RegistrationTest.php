<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Registration', function () {
    it('renders registration page', function () {
        // Assuming Fortify registration route is /register
        $this->get('/register')
            ->assertOk()
            ->assertSee('Register');
    });

    it('allows new user to register', function () {
        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'name' => 'New User',
        ]);

        $this->assertAuthenticated();
    });

    it('validates registration input', function () {
        $this->post('/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ])->assertSessionHasErrors(['name', 'email', 'password']);
    });
});
