<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Francis',
            'email' => 'moreno.francis@gmail.com',
            'password' => Hash::make('Admin#123!!'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Cecilia',
            'email' => 'adel.moreno56@gmail.com',
            'password' => Hash::make('Edge4wing'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'role' => 'admin',
        ]);
    }
}
