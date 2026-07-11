<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Exports\ExportRentalReport;
use App\Actions\Reports\BuildRentalReport;
use App\Enums\UserRole;
use App\Filament\Pages\RentalReport;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class RentalReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_filters_rentals_and_calculates_summary(): void
    {
        $inventory = $this->inventory();
        $active = $this->rental($inventory, '2026-06-10', 'rented', 2, 300);
        $this->rental($inventory, '2026-06-12', 'returned', 3, 450);
        $this->rental($inventory, '2026-07-10', 'rented', 4, 600);

        $report = app(BuildRentalReport::class)->execute(2026, 6, 'rented');

        $this->assertSame([$active->id], $report['rentals']->pluck('id')->all());
        $this->assertSame(1, $report['rentalCount']);
        $this->assertSame(2, $report['totalQuantity']);
        $this->assertSame(1, $report['activeCount']);
        $this->assertSame(300.0, $report['totalIncome']);
    }

    public function test_staff_can_view_filtered_rental_report(): void
    {
        $inventory = $this->inventory();
        $this->rental($inventory, '2026-06-10', 'rented', 2, 300);
        $staff = User::factory()->create(['role' => UserRole::Staff->value]);

        $this->actingAs($staff)->get('/admin/reports/rentals?year=2026&month=6&status=rented')
            ->assertOk()
            ->assertSee('Rental Report')
            ->assertSee('Folding Chairs')
            ->assertSee('Juan Dela Cruz')
            ->assertSee('PHP 300.00');
    }

    public function test_excel_export_contains_only_filtered_rentals(): void
    {
        $inventory = $this->inventory();
        $this->rental($inventory, '2026-06-10', 'rented', 2, 300);
        $this->rental($inventory, '2026-07-10', 'returned', 4, 600, 'Other Renter');

        $worksheet = $this->worksheetXml(app(ExportRentalReport::class)->execute(2026, 6, 'rented'));

        $this->assertStringContainsString('Rental Report', $worksheet);
        $this->assertStringContainsString('Folding Chairs', $worksheet);
        $this->assertStringContainsString('Juan Dela Cruz', $worksheet);
        $this->assertStringContainsString('<v>300.00</v>', $worksheet);
        $this->assertStringNotContainsString('Other Renter', $worksheet);
        $this->assertTrue((new \DOMDocument)->loadXML($worksheet));
    }

    public function test_filament_page_downloads_excel_with_active_filters(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff->value]);

        Livewire::actingAs($staff)->test(RentalReport::class)
            ->set('year', 2026)
            ->set('month', 6)
            ->set('status', 'returned')
            ->call('exportExcel')
            ->assertFileDownloaded('rental-report-2026-06-returned.xlsx');
    }

    private function inventory(): Inventory
    {
        return Inventory::create(['item_name' => 'Folding Chairs', 'total_quantity' => 20, 'available_quantity' => 10, 'rental_rate' => 150]);
    }

    private function rental(Inventory $inventory, string $date, string $status, int $quantity, float $amount, string $renter = 'Juan Dela Cruz'): Rental
    {
        $rental = Rental::create([
            'inventory_id' => $inventory->id,
            'renter_name' => $renter,
            'renter_contact' => '09123456789',
            'quantity' => $quantity,
            'rent_date' => $date,
            'return_date' => $status === 'returned' ? $date : null,
            'status' => $status,
        ]);
        Income::create(['date' => $date, 'source' => 'Rentals - Chairs', 'description' => 'Rental income', 'amount' => $amount, 'rental_id' => $rental->id]);

        return $rental;
    }

    private function worksheetXml(string $workbook): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rental-report-test-');
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
