<?php

namespace App\Enums;

/**
 * Whether the person still works here. Contract type is a separate concern.
 */
enum EmploymentStatus: string
{
    case Active = 'active';
    case Resigned = 'resigned';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Resigned => 'Resign',
            self::Terminated => 'Diberhentikan',
        };
    }

    /**
     * Colour used by the Filament badge column.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Resigned => 'warning',
            self::Terminated => 'danger',
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
