<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Exports\ExportRentalReport;
use App\Actions\Reports\BuildRentalReport;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class RentalReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Rentals';

    protected static ?int $navigationSort = 40;

    protected static ?string $slug = 'reports/rentals';

    protected string $view = 'filament.pages.rental-report';

    public int $year;

    public ?int $month = null;

    public ?string $status = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-cashflow-reports') ?? false;
    }

    public function mount(): void
    {
        $this->year = max(1, (int) request()->query('year', now()->year));
        $month = request()->query('month');
        $this->month = filled($month) ? max(1, min(12, (int) $month)) : null;
        $status = request()->query('status');
        $this->status = in_array($status, ['rented', 'returned'], true) ? $status : null;
    }

    /** @return array<string, mixed> */
    public function report(): array
    {
        return app(BuildRentalReport::class)->execute($this->year, $this->month, $this->status);
    }

    public function exportExcel(ExportRentalReport $export): StreamedResponse
    {
        $period = $this->month === null ? (string) $this->year : $this->year.'-'.str_pad((string) $this->month, 2, '0', STR_PAD_LEFT);
        $status = $this->status === null ? 'all' : $this->status;

        return response()->streamDownload(
            fn () => print $export->execute($this->year, $this->month, $this->status),
            'rental-report-'.$period.'-'.$status.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}
