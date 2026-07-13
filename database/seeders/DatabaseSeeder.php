<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\OAuth\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Equipment\ErrorMonitoring\Seeders\EquipmentErrorLogSeeder;
use Modules\Equipment\ErrorMonitoring\Seeders\OperatingTimeSeeder;
use Modules\Masterdata\Equipment\Seeders\EquipmentSeeder;

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

        $this->call(EquipmentSeeder::class);
        $this->call(EquipmentErrorLogSeeder::class);
        $this->call(OperatingTimeSeeder::class);

        // Create the default public OAuth client for Eamo Frontend if it does not exist
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
