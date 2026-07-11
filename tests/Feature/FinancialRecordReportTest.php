<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Exports\ExportFinancialRecordReport;
use App\Actions\Reports\BuildFinancialRecordReport;
use App\Enums\FinancialReportType;
use App\Enums\UserRole;
use App\Filament\Pages\ExpenseReport;
use App\Filament\Pages\IncomeReport;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class FinancialRecordReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_filter_records_and_total_by_year_and_month(): void
    {
        $user = User::factory()->create();
        Expense::create(['date' => '2026-06-10', 'category' => 'Supplies', 'description' => 'Paper', 'amount' => 125, 'created_by' => $user->id]);
        Expense::create(['date' => '2026-07-10', 'category' => 'Utilities', 'description' => 'Power', 'amount' => 300, 'created_by' => $user->id]);
        Income::create(['date' => '2026-06-12', 'source' => 'Donation', 'description' => 'Sponsor', 'amount' => 500]);
        Income::create(['date' => '2025-06-12', 'source' => 'Rental', 'description' => 'Chairs', 'amount' => 200]);

        $expenses = app(BuildFinancialRecordReport::class)->execute(FinancialReportType::Expense, 2026, 6);
        $income = app(BuildFinancialRecordReport::class)->execute(FinancialReportType::Income, 2026);

        $this->assertSame(125.0, $expenses['total']);
        $this->assertSame(['Paper'], $expenses['records']->pluck('description')->all());
        $this->assertSame(500.0, $income['total']);
        $this->assertSame(['Sponsor'], $income['records']->pluck('description')->all());
    }

    public function test_treasurer_can_view_income_and_expense_reports(): void
    {
        $treasurer = User::factory()->create(['role' => UserRole::Treasurer->value]);

        $this->actingAs($treasurer)->get('/admin/reports/income?year=2026&month=6')
            ->assertOk()->assertSee('Income Report')->assertSee('Generate Excel');
        $this->actingAs($treasurer)->get('/admin/reports/expenses?year=2026&month=6')
            ->assertOk()->assertSee('Expense Report')->assertSee('Generate Excel');
    }

    public function test_staff_can_view_financial_record_reports(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff->value]);

        $this->actingAs($staff)->get('/admin/reports/income')->assertOk()->assertSee('Income Report');
        $this->actingAs($staff)->get('/admin/reports/expenses')->assertOk()->assertSee('Expense Report');
    }

    public function test_excel_export_contains_filtered_financial_records(): void
    {
        Income::create(['date' => '2026-06-12', 'source' => 'Donation & Drive', 'description' => 'Sponsor', 'amount' => 500]);
        Income::create(['date' => '2026-07-12', 'source' => 'Rental', 'description' => 'Chairs', 'amount' => 200]);

        $workbook = app(ExportFinancialRecordReport::class)->execute(FinancialReportType::Income, 2026, 6);
        $worksheet = $this->worksheetXml($workbook);

        $this->assertStringContainsString('Income Report', $worksheet);
        $this->assertStringContainsString('Donation &amp; Drive', $worksheet);
        $this->assertStringContainsString('Sponsor', $worksheet);
        $this->assertStringContainsString('<v>500.00</v>', $worksheet);
        $this->assertStringNotContainsString('Chairs', $worksheet);
        $this->assertTrue((new \DOMDocument)->loadXML($worksheet));
    }

    public function test_filament_pages_download_excel_with_selected_period(): void
    {
        $treasurer = User::factory()->create(['role' => UserRole::Treasurer->value]);

        Livewire::actingAs($treasurer)->test(IncomeReport::class)
            ->set('year', 2026)->set('month', 6)->call('exportExcel')
            ->assertFileDownloaded('income-report-2026-06.xlsx');
        Livewire::actingAs($treasurer)->test(ExpenseReport::class)
            ->set('year', 2026)->set('month', null)->call('exportExcel')
            ->assertFileDownloaded('expense-report-2026.xlsx');
    }

    private function worksheetXml(string $workbook): string
    {
        $path = tempnam(sys_get_temp_dir(), 'financial-report-test-');
        $this->assertNotFalse($path);
        file_put_contents($path, $workbook);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);
        $this->assertNotFalse($worksheet);

        return (string) $worksheet;
    }
}
