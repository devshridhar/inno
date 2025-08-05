<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        $user = User::updateOrCreate(
            ['email' => 'demo@newsaggregator.com'],
            [
                'name' => 'Demo User',
                'first_name' => 'Demo',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Create user preferences
        UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_categories' => [1, 2], // technology, business
                'language' => 'en',
                'country' => 'us',
                'articles_per_page' => 20,
                'email_notifications' => false,
            ]
        );
    }
}
