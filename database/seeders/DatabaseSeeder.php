<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $companies = [
            [
                'name' => 'Acme Corp (Indeed via serial)',
                'serial_number' => 'S-001',
                'airwork_enabled' => false,
                'email' => 'hr.entry.office+1@example.com',
            ],
            [
                'name' => 'Globex (Custom Indeed Login)',
                'serial_number' => null,
                'airwork_enabled' => false,
                'email' => 'globex-hr@example.com',
            ],
            [
                'name' => 'Initech (Airwork Enabled)',
                'serial_number' => null,
                'airwork_enabled' => true,
                'email' => null,
            ],
            [
                'name' => 'Stark Industries (Working Indeed)',
                'serial_number' => 'S-002',
                'airwork_enabled' => false,
                'email' => 'stark-jobs@example.com',
            ],
            [
                'name' => 'Umbrella Corp (Broken - No Email / No Serial)',
                'serial_number' => null,
                'airwork_enabled' => false,
                'email' => null,
            ],
            [
                'name' => 'Wayne Enterprises (Airwork + Legacy Serial)',
                'serial_number' => 'S-003',
                'airwork_enabled' => true,
                'email' => 'wayne@example.com',
            ],
            [
                'name' => 'Cyberdyne Systems (Indeed Incomplete)',
                'serial_number' => null,
                'airwork_enabled' => false,
                'email' => '',
            ],
            [
                'name' => 'Massive Dynamic (Working Airwork)',
                'serial_number' => null,
                'airwork_enabled' => true,
                'email' => null,
            ],
            [
                'name' => 'Aperture Science (Indeed Serial Mismatch)',
                'serial_number' => 'S-999',
                'airwork_enabled' => false,
                'email' => 'aperture@example.com',
            ],
            [
                'name' => 'Hooli (Unassigned / Orphaned State)',
                'serial_number' => null,
                'airwork_enabled' => false,
                'email' => null,
            ],
        ];

        foreach ($companies as $data) {
            // Use serial_number as the unique lookup key if it exists, otherwise fall back to name
            $attributes = $data['serial_number']
                ? ['serial_number' => $data['serial_number']]
                : ['name' => $data['name']];

            $company = Company::query()->updateOrCreate(
                $attributes,
                [
                    'name' => $data['name'],
                    'airwork_enabled' => $data['airwork_enabled'],
                    'serial_number' => $data['serial_number'],
                ]
            );

            // Handle relation safely
            if (!empty($data['email'])) {
                $company->indeedCredentials()->updateOrCreate(
                    ['company_id' => $company->id],
                    ['email' => $data['email']]
                );
            } else {
                $company->indeedCredentials()->delete();
            }
        }
    }
}
