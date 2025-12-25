<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a consistent admin user
        User::factory()->create([
            'name' => 'SourceGuardian Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
        ]);

        // Create a variety of other users
        User::factory(10)->create();

        // Run the ProjectSeeder to create a variety of projects and their related data
        $this->call(ProjectSeeder::class);
    }
}
