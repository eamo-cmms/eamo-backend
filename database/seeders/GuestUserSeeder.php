<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::first();

        User::updateOrCreate(
            ['email' => 'guest@gmail.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make(config('auth.seed_passwords.guest', '12345678')),
                'role' => UserRole::Guest,
                'department_id' => $department?->id,
            ]
        );
    }
}
