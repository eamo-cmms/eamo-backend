<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Department;
use App\Models\OAuth\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Equipment\ErrorMonitoring\Seeders\OperatingTimeSeeder;
use Modules\Equipment\ParameterLog\Seeders\EquipmentParameterLogSeeder;
use Modules\Masterdata\Equipment\Seeders\EquipmentSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Permissions
        $this->call(PermissionSeeder::class);

        // 2. Organization structure
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

        // 3. Four standard role accounts (password: 12345678)
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => $password,
            'role' => UserRole::Admin,
            'department_id' => $techDepartment->id,
        ]);

        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => $password,
            'role' => UserRole::Manager,
            'department_id' => $techDepartment->id,
        ]);

        User::factory()->create([
            'name' => 'Engineer User',
            'email' => 'engineer@gmail.com',
            'password' => $password,
            'role' => UserRole::Engineer,
            'department_id' => $techDepartment->id,
        ]);

        User::factory()->create([
            'name' => 'Guest User',
            'email' => 'guest@gmail.com',
            'password' => $password,
            'role' => UserRole::Guest,
            'department_id' => $techDepartment->id,
        ]);

        // 50 Random Users distributed across departments
        User::factory()->count(50)->sequence(fn () => [
            'department_id' => $allDepartments->random()->id,
        ])->create([
            'password' => $password,
        ]);

        // 4. Module Seeders
        $this->call(EquipmentSeeder::class);
        $this->call(MaintenanceCategorySeeder::class);
        $this->call(OperatingTimeSeeder::class);
        $this->call(EquipmentParameterLogSeeder::class);

        // 5. Create the default public OAuth client for Eamo Frontend if it does not exist
        Client::updateOrCreate(
            ['id' => '019f3598-1773-73aa-b922-377675fd2b7f'],
            [
                'name' => 'Eamo Frontend',
                'secret' => null,
                'provider' => null,
                'redirect_uris' => ['http://localhost:5173/auth/callback'],
                'grant_types' => ['authorization_code', 'refresh_token'],
                'revoked' => false,
            ]
        );
    }
}
