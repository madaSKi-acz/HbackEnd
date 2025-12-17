<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create default Admin user
        User::create([
            'name' => 'Admin',
            'phone' => '123',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123'), // hash the password
        ]);

        // Optionally, still create 10 random users from factory
        User::factory()->count(10)->create();
    }
}
