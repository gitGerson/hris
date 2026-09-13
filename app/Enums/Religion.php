<?php

namespace App\Enums;

/**
 * The six religions recognised on Indonesian civil records.
 */
enum Religion: string
{
    case Islam = 'islam';
    case Protestantism = 'protestantism';
    case Catholicism = 'catholicism';
    case Hinduism = 'hinduism';
    case Buddhism = 'buddhism';
    case Confucianism = 'confucianism';

    public function label(): string
    {
        return match ($this) {
            self::Islam => 'Islam',
            self::Protestantism => 'Kristen Protestan',
            self::Catholicism => 'Katolik',
            self::Hinduism => 'Hindu',
            self::Buddhism => 'Buddha',
            self::Confucianism => 'Konghucu',
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
