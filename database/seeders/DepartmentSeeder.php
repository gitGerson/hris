<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the starting departments.
     *
     * Keyed on the unique code, so re-running updates instead of duplicating.
     * company_id is set explicitly: DatabaseSeeder runs with WithoutModelEvents,
     * so the model's creating hook does not fire here.
     */
    public function run(): void
    {
        $companyId = Company::current()->id;

        $departments = [
            'PRD' => 'Production',
            'OUT' => 'Outlet',
            'SLS' => 'Sales & Marketing',
            'FIN' => 'Finance & Accounting',
            'HRD' => 'Human Resources',
            'LOG' => 'Logistics & Warehouse',
        ];

        foreach ($departments as $code => $name) {
            Department::updateOrCreate(
                ['code' => $code],
                [
                    'company_id' => $companyId,
                    'name' => $name,
                    'is_active' => true,
                ],
            );
        }
    }
}
