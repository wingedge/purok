<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Dashboard\BuildDashboardSummary;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class DashboardSummary extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard Summary';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'dashboard-summary';

    protected string $view = 'filament.pages.dashboard-summary';

    public int $year;

    public ?int $month = null;

    /** @var array<string, float|int> */
    public array $summary = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-dashboard') ?? false;
    }

    public function mount(): void
    {
        $this->year = (int) request()->query('year', now()->year);
        $month = request()->query('month');
        $this->month = filled($month) ? max(1, min(12, (int) $month)) : null;

        $this->summary = app(BuildDashboardSummary::class)->execute($this->year, $this->month);
    }

    public function periodLabel(): string
    {
        if ($this->month === null) {
            return (string) $this->year;
        }

        return \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }
}
