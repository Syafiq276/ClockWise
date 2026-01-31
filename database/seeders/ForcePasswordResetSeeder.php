<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForcePasswordResetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@goolee.my'; // The email you are trying to login with
        $newPassword = 'Goolee@2026!'; // The password you want to use

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'password' => Hash::make($newPassword),
            ]);
            Log::info("PASSWORD RESET SUCCESS: Password for {$email} has been updated.");
            $this->command->info("PASSWORD RESET SUCCESS: Password for {$email} has been updated.");
        } else {
            Log::error("PASSWORD RESET FAILED: User {$email} not found.");
            $this->command->error("PASSWORD RESET FAILED: User {$email} not found.");
        }
    }
}
