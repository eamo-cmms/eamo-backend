<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $company = Company::create([
            'name' => 'Ouransoft',
            'contact' => '0123456789',
        ]);

        $techDepartment = Department::create([
            'company_id' => $company->id,
            'name' => 'Technology',
            'contact' => 'tech@ouransoft.com',
        ]);

        $additionalDepartments = [
            Department::create(['company_id' => $company->id, 'name' => 'Human Resources', 'contact' => 'hr@ouransoft.com']),
            Department::create(['company_id' => $company->id, 'name' => 'Finance', 'contact' => 'finance@ouransoft.com']),
            Department::create(['company_id' => $company->id, 'name' => 'Marketing', 'contact' => 'marketing@ouransoft.com']),
            Department::create(['company_id' => $company->id, 'name' => 'Sales', 'contact' => 'sales@ouransoft.com']),
        ];

        $allDepartments = collect([$techDepartment])->concat($additionalDepartments);

        $password = Hash::make('12345678');

        // Admin User
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => $password,
            'role' => 'admin',
            'department_id' => $techDepartment->id,
        ]);

        // 50 Random Users distributed across departments
        User::factory()->count(50)->sequence(fn () => [
            'department_id' => $allDepartments->random()->id,
        ])->create([
            'password' => $password,
        ]);
    }
}
