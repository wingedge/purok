<?php

declare(strict_types=1);

namespace App\Support\Finance;

final class ExpenseCategories
{
    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            'Operating Expenses	Supplies, Utility Bills, Communication',
            'Community Services	Health Programs, Feeding, Cleanup Drives',
            'Social Benefits, Burial Aid, Emergency Medical Assistance',
            'Activities, Peace & Security	Patrol Supplies, Volunteers Meals',
            'Special Projects, Fiesta/Christmas Events, Minor Repairs',
            'Misc',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::values())
            ->mapWithKeys(fn (string $category): array => [$category => $category])
            ->all();
    }
}
