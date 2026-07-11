<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\FinancialReportType;
use App\Filament\Pages\Concerns\InteractsWithFinancialRecordReport;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ExpenseReport extends Page
{
    use InteractsWithFinancialRecordReport;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Expenses';

    protected static ?int $navigationSort = 30;

    protected static ?string $slug = 'reports/expenses';

    protected string $view = 'filament.pages.financial-record-report';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-cashflow-reports') ?? false;
    }

    public function reportType(): FinancialReportType
    {
        return FinancialReportType::Expense;
    }
}
