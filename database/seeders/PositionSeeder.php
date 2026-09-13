<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Seed the starting positions, grouped under their department.
     *
     * Keyed on the unique code, so re-running updates instead of duplicating.
     * department_id is set explicitly: DatabaseSeeder runs with WithoutModelEvents,
     * and the column is not nullable.
     *
     * @var array<string, array<string, string>>
     */
    protected array $positionsByDepartment = [
        'PRD' => [
            'PRD-SPV' => 'Production Supervisor',
            'PRD-BKR' => 'Baker',
            'PRD-PSY' => 'Pastry Chef',
        ],
        'OUT' => [
            'OUT-SPV' => 'Outlet Supervisor',
            'OUT-CSR' => 'Cashier',
            'OUT-CRW' => 'Store Crew',
        ],
        'SLS' => [
            'SLS-SPV' => 'Sales Supervisor',
            'SLS-STF' => 'Sales Staff',
        ],
        'FIN' => [
            'FIN-SPV' => 'Finance Supervisor',
            'FIN-ACC' => 'Accounting Staff',
        ],
        'HRD' => [
            'HRD-SPV' => 'HR Supervisor',
            'HRD-STF' => 'HR Staff',
        ],
        'LOG' => [
            'LOG-WHS' => 'Warehouse Staff',
            'LOG-DRV' => 'Driver',
        ],
    ];

    public function run(): void
    {
        $departmentIds = Department::query()
            ->whereIn('code', array_keys($this->positionsByDepartment))
            ->pluck('id', 'code');

        foreach ($this->positionsByDepartment as $departmentCode => $positions) {
            $departmentId = $departmentIds[$departmentCode] ?? null;

            if ($departmentId === null) {
                $this->command?->warn("Department [{$departmentCode}] not found, skipping its positions. Run DepartmentSeeder first.");

                continue;
            }

            foreach ($positions as $code => $name) {
                Position::updateOrCreate(
                    ['code' => $code],
                    [
                        'department_id' => $departmentId,
                        'name' => $name,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
