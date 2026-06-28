<?php

declare(strict_types=1);

namespace App\Support\Community;

final class OfficerPositions
{
    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            'President',
            'Vice President',
            'Secretary',
            'Treasurer',
            'Auditor',
            'PIO',
            'Peace and Order Officer',
            'Committee Chair',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::values())
            ->mapWithKeys(fn (string $position): array => [$position => $position])
            ->all();
    }
}
