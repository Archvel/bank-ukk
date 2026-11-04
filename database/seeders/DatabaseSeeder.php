<?php

namespace Database\Seeders;

use App\Enums\Role;
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
        $this->call(AccountSeeder::class);

        // 1. Admin & Supervisor User
        $admin = User::firstOrCreate(
            ['email' => 'admin@bankmini.test'],
            [
                'name' => 'Administrator & Supervisor',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'is_active' => true,
            ]
        );

        // 2. Piket Teller User
        $teller = User::firstOrCreate(
            ['email' => 'teller@bankmini.test'],
            [
                'name' => 'Piket Teller 1',
                'username' => 'teller1',
                'password' => Hash::make('password'),
                'role' => Role::Teller,
                'is_active' => true,
            ]
        );

        // 3. Import and seed student members (all set to inactive accounts)
        $this->call(StudentDataSeeder::class);
    }
}
