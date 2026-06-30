<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Exports\ExportContributionReport;
use App\Actions\Reports\BuildContributionReport;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class ContributionReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Contributions';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'reports/contributions';

    protected string $view = 'filament.pages.contribution-report';

    public int $year;

    public int $startMonth;

    public int $endMonth;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-contribution-reports') ?? false;
    }

    public function mount(): void
    {
        $this->year = (int) request()->query('year', now()->year);
        $this->startMonth = max(1, min(12, (int) request()->query('start_month', 1)));
        $this->endMonth = max(1, min(12, (int) request()->query('end_month', 12)));
    }

    /**
     * @return array<string, mixed>
     */
    public function report(): array
    {
        return app(BuildContributionReport::class)->execute(
            $this->year,
            $this->startMonth,
            $this->endMonth,
        );
    }

    public function exportExcel(ExportContributionReport $exportContributionReport): StreamedResponse
    {
        $workbook = $exportContributionReport->execute(
            $this->year,
            $this->startMonth,
            $this->endMonth,
        );

        return response()->streamDownload(
            fn () => print $workbook,
            'contribution-report-'.$this->year.'-'.str_pad((string) $this->startMonth, 2, '0', STR_PAD_LEFT)
                .'-'.str_pad((string) $this->endMonth, 2, '0', STR_PAD_LEFT).'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}
