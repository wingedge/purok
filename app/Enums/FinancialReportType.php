<?php

declare(strict_types=1);

namespace App\Enums;

enum FinancialReportType: string
{
    case Expense = 'expense';
    case Income = 'income';

    public function title(): string
    {
        return match ($this) {
            self::Expense => 'Expense Report',
            self::Income => 'Income Report',
        };
    }

    public function classificationLabel(): string
    {
        return match ($this) {
            self::Expense => 'Category',
            self::Income => 'Source',
        };
    }
}
