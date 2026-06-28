<?php

declare(strict_types=1);

namespace App\Support\Finance;

final class IncomeSources
{
    public const RENTAL = 'Rentals - Chairs / Table rental';

    public static function rental(): string
    {
        return self::RENTAL;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::RENTAL,
            'Donation / Fund Drive',
            'Commission / Incentive',
            'Government Aid',
            'Penalties',
            'Misc',
            'Cash on Hand',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::values())
            ->mapWithKeys(fn (string $source): array => [$source => $source])
            ->all();
    }
}
