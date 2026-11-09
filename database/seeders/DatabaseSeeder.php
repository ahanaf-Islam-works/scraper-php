<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Company::query()->updateOrCreate(
            ['serial_number' => 'S-001'],
            ['name' => 'Acme (Indeed via serial)', 'airwork_enabled' => false],
        );

        $globex = Company::query()->updateOrCreate(
            ['name' => 'Globex (custom Indeed login)'],
            ['serial_number' => null, 'airwork_enabled' => false],
        );

        $globex->indeedCredentials()->updateOrCreate(
            ['company_id' => $globex->id],
            ['email' => 'globex-hr@example.com'],
        );

        Company::query()->updateOrCreate(
            ['name' => 'Initech (Airwork)'],
            ['serial_number' => null, 'airwork_enabled' => true],
        );
    }
}
