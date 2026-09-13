<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\Religion;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'employee_number', 'name', 'phone', 'national_id', 'gender', 'religion',
    'marital_status', 'birth_place', 'birth_date',
    'identity_address', 'identity_province_id', 'identity_city_id',
    'domicile_address', 'domicile_province_id', 'domicile_city_id',
    'branch_id', 'department_id', 'position_id', 'job_level_id',
    'join_date', 'contract_ends_on', 'employment_status', 'termination_date',
    'fingerprint_id',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /** Prefix for the generated employee number. */
    public const NUMBER_PREFIX = 'RR';

    /** Digits after the prefix, so RR0001. */
    public const NUMBER_PADDING = 4;

    /**
     * Mirrors the column default, so a new instance reads 'active' before it is refreshed.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'employment_status' => EmploymentStatus::Active->value,
    ];

    protected static function booted(): void
    {
        static::creating(function (Employee $employee): void {
            $employee->employee_number ??= static::generateEmployeeNumber();
        });
    }

    /**
     * Next number in the RR0001 series.
     *
     * Callable directly because seeders run with WithoutModelEvents, where the
     * creating hook above never fires. Racy under concurrent inserts; the unique
     * index on employee_number is what actually guarantees correctness.
     */
    public static function generateEmployeeNumber(): string
    {
        $latest = static::withTrashed()
            ->where('employee_number', 'like', static::NUMBER_PREFIX.'%')
            ->orderByDesc('employee_number')
            ->value('employee_number');

        $sequence = $latest === null
            ? 0
            : (int) substr($latest, strlen(static::NUMBER_PREFIX));

        return static::NUMBER_PREFIX.str_pad((string) ($sequence + 1), static::NUMBER_PADDING, '0', STR_PAD_LEFT);
    }

    /**
     * Identity numbers and addresses are deliberately kept out of the activity log.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['national_id', 'phone', 'identity_address', 'domicile_address', 'fingerprint_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('employment_status', EmploymentStatus::Active);
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @return BelongsTo<JobLevel, $this>
     */
    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class);
    }

    /**
     * @return BelongsTo<Province, $this>
     */
    public function identityProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'identity_province_id');
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function identityCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'identity_city_id');
    }

    /**
     * @return BelongsTo<Province, $this>
     */
    public function domicileProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'domicile_province_id');
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function domicileCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'domicile_city_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'religion' => Religion::class,
            'marital_status' => MaritalStatus::class,
            'employment_status' => EmploymentStatus::class,
            'birth_date' => 'date',
            'join_date' => 'date',
            'contract_ends_on' => 'date',
            'termination_date' => 'date',
        ];
    }
}
