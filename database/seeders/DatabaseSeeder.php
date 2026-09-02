<?php

namespace Database\Seeders;

use App\Models\Role;
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
        $this->call([
            RoleSeeder::class,
            GroceryItemSeeder::class,
        ]);

        $adminRole = Role::where('slug', Role::ADMIN)->first();
        $userRole = Role::where('slug', Role::USER)->first();

        User::firstOrCreate(
            ['email' => 'admin@grocery.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@grocery.com'],
            [
                'name' => 'Grocery Customer',
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id,
            ]
        );
    }
}
