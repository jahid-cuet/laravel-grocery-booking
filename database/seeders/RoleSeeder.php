<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['slug' => Role::ADMIN],
            [
                'name' => 'Admin',
                'description' => 'Administrator with full catalogue and stock management permissions',
            ]
        );

        Role::firstOrCreate(
            ['slug' => Role::USER],
            [
                'name' => 'User',
                'description' => 'Regular user who can browse groceries and book orders',
            ]
        );
    }
}
