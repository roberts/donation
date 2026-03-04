<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use App\Livewire\DonationForm;
use Illuminate\Support\Facades\Route;

// Homepage is the donation form
Route::get('/', DonationForm::class)->name('home');

// Public Donation Routes
Route::prefix('donate')->name('donation.')->group(function () {
    Route::get('/', DonationForm::class)->name('create');
    Route::get('/thank-you', function () {
        return view('donation.success');
    })->name('success');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/two-factor-authentication', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');
});
