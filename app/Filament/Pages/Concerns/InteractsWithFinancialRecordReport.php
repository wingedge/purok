<?php

declare(strict_types=1);

namespace App\Filament\Pages\Concerns;

use App\Actions\Exports\ExportFinancialRecordReport;
use App\Actions\Reports\BuildFinancialRecordReport;
use App\Enums\FinancialReportType;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait InteractsWithFinancialRecordReport
{
    public int $year;

    public ?int $month = null;

    public function mount(): void
    {
        $this->year = max(1, (int) request()->query('year', now()->year));
        $month = request()->query('month');
        $this->month = filled($month) ? max(1, min(12, (int) $month)) : null;
    }

    /** @return array<string, mixed> */
    public function report(): array
    {
        return app(BuildFinancialRecordReport::class)->execute($this->reportType(), $this->year, $this->month);
    }

    public function exportExcel(ExportFinancialRecordReport $export): StreamedResponse
    {
        $workbook = $export->execute($this->reportType(), $this->year, $this->month);
        $period = $this->month === null ? (string) $this->year : $this->year.'-'.str_pad((string) $this->month, 2, '0', STR_PAD_LEFT);

        return response()->streamDownload(
            fn () => print $workbook,
            $this->reportType()->value.'-report-'.$period.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }

    abstract public function reportType(): FinancialReportType;
}
