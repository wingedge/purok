<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\FinancialReportType;
use App\Filament\Pages\Concerns\InteractsWithFinancialRecordReport;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class IncomeReport extends Page
{
    use InteractsWithFinancialRecordReport;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Income';

    protected static ?int $navigationSort = 25;

    protected static ?string $slug = 'reports/income';

    protected string $view = 'filament.pages.financial-record-report';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-cashflow-reports') ?? false;
    }

    public function reportType(): FinancialReportType
    {
        return FinancialReportType::Income;
    }
}
