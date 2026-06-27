<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Reports\BuildCashFlowReport;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CashFlowReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Cash Flow';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'reports/cash-flow';

    protected string $view = 'filament.pages.cash-flow-report';

    public int $year;

    public ?int $month = null;

    /** @var array<string, float> */
    public array $report = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-cashflow-reports') ?? false;
    }

    public function mount(): void
    {
        $this->year = (int) request()->query('year', now()->year);
        $month = request()->query('month');
        $this->month = filled($month) ? (int) $month : null;
        $this->report = app(BuildCashFlowReport::class)->execute($this->year, $this->month);
    }
}
