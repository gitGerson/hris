<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Seed the operating company and its head office branch.
     *
     * Keyed on the unique code, so re-running updates instead of duplicating.
     */
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['code' => 'RR'],
            [
                'name' => 'Rumah Roti',
                'legal_name' => 'PT Rumah Roti',
                'country' => 'ID',
                'is_active' => true,
            ],
        );

        Branch::updateOrCreate(
            ['code' => 'RR-HO'],
            [
                'company_id' => $company->id,
                'name' => 'Head Office',
                'type' => 'head_office',
                'timezone' => 'Asia/Jakarta',
                'geofence_radius' => 100,
                'is_active' => true,
            ],
        );
    }
}
