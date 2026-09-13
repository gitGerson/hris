<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\Religion;
use App\Models\Branch;
use App\Models\City;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\Position;
use App\Models\Province;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Starting staff for the head office branch.
     *
     * Keyed on phone, which is the attendance binding key and unique. The employee
     * number cannot be the key because it is generated, so it is assigned explicitly:
     * DatabaseSeeder runs with WithoutModelEvents, where the creating hook never fires.
     *
     * @var array<int, array{name: string, phone: string, position: string, level: string, gender: Gender, religion: Religion, marital_status: MaritalStatus, birth_date: string, join_date: string}>
     */
    protected array $employees = [
        [
            'name' => 'Slamet Riyadi',
            'phone' => '081234560001',
            'position' => 'PRD-SPV',
            'level' => 'SPV',
            'gender' => Gender::Male,
            'religion' => Religion::Islam,
            'marital_status' => MaritalStatus::Married,
            'birth_date' => '1988-04-12',
            'join_date' => '2021-03-01',
        ],
        [
            'name' => 'Dwi Lestari',
            'phone' => '081234560002',
            'position' => 'PRD-BKR',
            'level' => 'STF',
            'gender' => Gender::Female,
            'religion' => Religion::Islam,
            'marital_status' => MaritalStatus::Married,
            'birth_date' => '1993-09-30',
            'join_date' => '2022-06-15',
        ],
        [
            'name' => 'Agus Setiawan',
            'phone' => '081234560003',
            'position' => 'PRD-PSY',
            'level' => 'STF',
            'gender' => Gender::Male,
            'religion' => Religion::Catholicism,
            'marital_status' => MaritalStatus::Single,
            'birth_date' => '1996-01-22',
            'join_date' => '2023-02-01',
        ],
        [
            'name' => 'Rina Puspitasari',
            'phone' => '081234560004',
            'position' => 'OUT-SPV',
            'level' => 'SPV',
            'gender' => Gender::Female,
            'religion' => Religion::Islam,
            'marital_status' => MaritalStatus::Married,
            'birth_date' => '1990-07-08',
            'join_date' => '2021-08-09',
        ],
        [
            'name' => 'Bayu Nugroho',
            'phone' => '081234560005',
            'position' => 'OUT-CSR',
            'level' => 'STF',
            'gender' => Gender::Male,
            'religion' => Religion::Islam,
            'marital_status' => MaritalStatus::Single,
            'birth_date' => '1999-11-17',
            'join_date' => '2024-01-15',
        ],
        [
            'name' => 'Siti Aminah',
            'phone' => '081234560006',
            'position' => 'OUT-CRW',
            'level' => 'JNR',
            'gender' => Gender::Female,
            'religion' => Religion::Islam,
            'marital_status' => MaritalStatus::Single,
            'birth_date' => '2002-05-03',
            'join_date' => '2025-02-03',
        ],
        [
            'name' => 'Hendra Wijaya',
            'phone' => '081234560007',
            'position' => 'FIN-ACC',
            'level' => 'STF',
            'gender' => Gender::Male,
            'religion' => Religion::Buddhism,
            'marital_status' => MaritalStatus::Married,
            'birth_date' => '1991-12-25',
            'join_date' => '2022-09-05',
        ],
        [
            'name' => 'Maya Anggraini',
            'phone' => '081234560008',
            'position' => 'HRD-STF',
            'level' => 'STF',
            'gender' => Gender::Female,
            'religion' => Religion::Protestantism,
            'marital_status' => MaritalStatus::Single,
            'birth_date' => '1995-03-19',
            'join_date' => '2023-07-17',
        ],
    ];

    public function run(): void
    {
        $branch = Branch::where('code', 'RR-HO')->first();

        if ($branch === null) {
            $this->command?->warn('Branch [RR-HO] not found, skipping employees. Run CompanySeeder first.');

            return;
        }

        $positions = Position::with('department')->get()->keyBy('code');
        $levels = JobLevel::pluck('id', 'code');

        $province = Province::where('code', '33')->first();
        $city = $province !== null
            ? City::where('province_id', $province->id)->where('code', '33.28')->first()
            : null;

        foreach ($this->employees as $employee) {
            $position = $positions[$employee['position']] ?? null;

            if ($position === null) {
                $this->command?->warn("Position [{$employee['position']}] not found, skipping {$employee['name']}. Run PositionSeeder first.");

                continue;
            }

            $existing = Employee::withTrashed()->where('phone', $employee['phone'])->first();

            Employee::updateOrCreate(
                ['phone' => $employee['phone']],
                [
                    'employee_number' => $existing?->employee_number ?? Employee::generateEmployeeNumber(),
                    'name' => $employee['name'],
                    'gender' => $employee['gender'],
                    'religion' => $employee['religion'],
                    'marital_status' => $employee['marital_status'],
                    'birth_place' => 'Tegal',
                    'birth_date' => $employee['birth_date'],
                    'identity_address' => 'Jl. Raya Lebaksiu No. '.random_int(1, 99),
                    'identity_province_id' => $province?->id,
                    'identity_city_id' => $city?->id,
                    'branch_id' => $branch->id,
                    'department_id' => $position->department_id,
                    'position_id' => $position->id,
                    'job_level_id' => $levels[$employee['level']] ?? null,
                    'join_date' => $employee['join_date'],
                    'employment_status' => EmploymentStatus::Active,
                ],
            );
        }

        $this->command?->info('Seeded '.Employee::count().' employees.');
    }
}
