<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Authentication', function () {
    it('renders login page', function () {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Sign in');
    });

    it('allows user to login with valid credentials', function () {
        $user = User::factory()->withoutTwoFactor()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/'); // Redirects to home route which is /

        $this->assertAuthenticatedAs($user);
    });

    it('prevents login with invalid credentials', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    });
});
