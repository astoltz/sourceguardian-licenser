<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This seeder is intended for development environments to populate the
     * database with a large amount of sample data for testing pagination
     * and other features.
     */
    public function run(): void
    {
        // Create a consistent admin user for development
        User::factory()->create([
            'name' => 'SourceGuardian Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
        ]);

        // Create 25 random users to test pagination
        User::factory(25)->create();

        // Run the ProjectSeeder to create a variety of projects and their related data
        $this->call(ProjectSeeder::class);
    }
}
