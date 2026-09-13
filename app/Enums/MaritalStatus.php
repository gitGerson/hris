<?php

namespace App\Enums;

/**
 * Drives PTKP tax status once payroll arrives.
 */
enum MaritalStatus: string
{
    case Single = 'single';
    case Married = 'married';
    case Divorced = 'divorced';
    case Widowed = 'widowed';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Belum Menikah',
            self::Married => 'Menikah',
            self::Divorced => 'Cerai Hidup',
            self::Widowed => 'Cerai Mati',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->all();
    }
}
