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

        // 3. Four standard role accounts (guest: 12345678, others: configured via env)
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make($this->getRolePassword(UserRole::Admin)),
            'role' => UserRole::Admin,
            'department_id' => $techDepartment->id,
        ]);

        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@gmail.com',
            'password' => Hash::make($this->getRolePassword(UserRole::Manager)),
            'role' => UserRole::Manager,
            'department_id' => $techDepartment->id,
        ]);

        User::factory()->create([
            'name' => 'Engineer User',
            'email' => 'engineer@gmail.com',
            'password' => Hash::make($this->getRolePassword(UserRole::Engineer)),
            'role' => UserRole::Engineer,
            'department_id' => $techDepartment->id,
        ]);

        User::factory()->create([
            'name' => 'Guest User',
            'email' => 'guest@gmail.com',
            'password' => Hash::make($this->getRolePassword(UserRole::Guest)),
            'role' => UserRole::Guest,
            'department_id' => $techDepartment->id,
        ]);

        // 4. Module Seeders
        $this->call(EquipmentSeeder::class);
        $this->call(EquipmentErrorSeeder::class);
        $this->call(MaintenanceCategorySeeder::class);
        $this->call(OperatingTimeSeeder::class);
        $this->call(EquipmentParameterLogSeeder::class);
        $this->call(MaintenanceLogSeeder::class);

        // 5. Create the default public OAuth client for Eamo Frontend if it does not exist
        $frontendUrl = rtrim((string) env('FRONTEND_URL'), '/');
        $redirectUris = array_values(array_unique([
            $frontendUrl . '/auth/callback',
            'https://eamo.io.vn/auth/callback',
            'https://www.eamo.io.vn/auth/callback',
            'http://localhost:5173/auth/callback',
        ]));

        Client::updateOrCreate(
            ['id' => '019f3598-1773-73aa-b922-377675fd2b7f'],
            [
                'name' => 'Eamo Frontend',
                'secret' => null,
                'provider' => null,
                'redirect_uris' => $redirectUris,
                'grant_types' => ['authorization_code', 'refresh_token'],
                'revoked' => false,
            ]
        );
    }

    /**
     * Get the seed password for a given user role.
     * Non-guest passwords must be configured in environment variables.
     * Guest user defaults to 12345678.
     */
    private function getRolePassword(UserRole $role): string
    {
        if ($role === UserRole::Guest) {
            return (string) config('auth.seed_passwords.guest', env('SEED_GUEST_PASSWORD', env('GUEST_PASSWORD', '12345678')));
        }

        $envKey = 'SEED_' . strtoupper($role->value) . '_PASSWORD';
        $fallbackEnvKey = strtoupper($role->value) . '_PASSWORD';

        $password = config("auth.seed_passwords.{$role->value}")
            ?: env($envKey)
            ?: env($fallbackEnvKey);

        if (empty($password)) {
            throw new \RuntimeException("Seed password for role [{$role->value}] is not defined. Please set {$envKey} in your .env file.");
        }

        return (string) $password;
    }
}
