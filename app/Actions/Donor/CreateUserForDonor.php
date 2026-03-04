<?php

declare(strict_types=1);

namespace App\Actions\Donor;

use App\Exceptions\Donor\DonorCreationException;
use App\Models\Donor;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserForDonor
{
    public function execute(Donor $donor): User
    {
        if ($donor->user_id) {
            return $donor->user;
        }

        $user = User::where('email', $donor->email)->first();

        if (! $user) {
            try {
                $password = Str::random(16);
                $user = User::create([
                    'name' => "{$donor->first_name} {$donor->last_name}",
                    'email' => $donor->email,
                    'password' => Hash::make($password),
                ]);
                $user->assignRole('donor');
            } catch (Exception $e) {
                throw DonorCreationException::failed('Could not create user for donor: '.$e->getMessage());
            }
        } else {
            if (! $user->hasRole('donor')) {
                $user->assignRole('donor');
            }
        }

        $donor->update(['user_id' => $user->id]);

        return $user;
    }
}
