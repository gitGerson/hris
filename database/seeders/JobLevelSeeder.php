<?php

namespace Database\Seeders;

use App\Models\JobLevel;
use Illuminate\Database\Seeder;

class JobLevelSeeder extends Seeder
{
    /**
     * Seed the grading ladder.
     *
     * Keyed on the unique code, so re-running updates instead of duplicating.
     * `level` is the rank used to compare seniority: lower is more senior.
     *
     * @var array<string, array{name: string, level: int}>
     */
    protected array $jobLevels = [
        'DIR' => ['name' => 'Director', 'level' => 1],
        'MGR' => ['name' => 'Manager', 'level' => 2],
        'SPV' => ['name' => 'Supervisor', 'level' => 3],
        'STF' => ['name' => 'Staff', 'level' => 4],
        'JNR' => ['name' => 'Junior Staff', 'level' => 5],
        'INT' => ['name' => 'Intern', 'level' => 6],
    ];

    public function run(): void
    {
        foreach ($this->jobLevels as $code => $jobLevel) {
            JobLevel::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $jobLevel['name'],
                    'level' => $jobLevel['level'],
                    'is_active' => true,
                ],
            );
        }
    }
}
