<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('Password Reset', function () {
    it('renders forgot password page', function () {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Email Password Reset Link');
    });

    it('sends password reset link', function () {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ])->assertSessionHas('status');

        // Fortify uses a notification to send the reset link
        // We can verify the notification was sent, but exact class depends on Laravel version/setup
        // Usually Illuminate\Auth\Notifications\ResetPassword
        Notification::assertSentTo($user, ResetPassword::class);
    });

    it('renders reset password page with token', function () {
        $token = 'valid-token';
        $this->get('/reset-password/'.$token)
            ->assertOk()
            ->assertSee('Reset Password');
    });

    it('resets password with valid token', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $token = app('auth.password.broker')->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect('/login');

        $this->assertTrue(auth()->attempt([
            'email' => 'test@example.com',
            'password' => 'new-password',
        ]));
    });
});
