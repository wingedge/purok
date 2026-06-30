<?php

namespace Tests\Feature;

use App\Actions\Exports\ExportContributionReport;
use App\Actions\Reports\BuildContributionReport;
use App\Enums\UserRole;
use App\Filament\Pages\ContributionReport;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class ContributionReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_contribution_report_action_calculates_member_and_report_totals(): void
    {
        $maria = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        $jose = Member::create([
            'name' => 'Jose Reyes',
            'indigent' => false,
        ]);
        $indigent = Member::create([
            'name' => 'Indigent Member',
            'indigent' => true,
        ]);

        Contribution::create([
            'member_id' => $maria->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);
        Contribution::create([
            'member_id' => $maria->id,
            'week_start' => '2026-06-14',
            'amount' => 10,
        ]);
        Contribution::create([
            'member_id' => $jose->id,
            'week_start' => '2026-07-05',
            'amount' => 10,
        ]);
        Contribution::create([
            'member_id' => $indigent->id,
            'week_start' => '2026-06-07',
            'amount' => 0,
        ]);

        $report = app(BuildContributionReport::class)->execute(2026, 6, 6);

        $this->assertSame('2026-06-01', $report['start']->toDateString());
        $this->assertSame('2026-06-30', $report['end']->toDateString());
        $this->assertSame(20.0, $report['memberTotals'][$maria->id]);
        $this->assertSame(0.0, $report['memberTotals'][$jose->id]);
        $this->assertSame(20.0, $report['reportTotal']);
        $this->assertFalse($report['members']->contains('id', $indigent->id));
    }

    public function test_old_contribution_report_redirects_to_filament_report(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/reports/contributions?year=2026&start_month=6&end_month=6')
            ->assertRedirect('/admin/reports/contributions?year=2026&start_month=6&end_month=6');
    }

    public function test_staff_can_view_filament_contribution_report(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $this->actingAs($this->userWithRole(UserRole::Staff))
            ->get('/admin/reports/contributions?year=2026&start_month=6&end_month=6')
            ->assertOk()
            ->assertSee('Member Contributions')
            ->assertSee('Maria Santos')
            ->assertSee('Total Contributions: PHP 10.00');
    }

    public function test_contribution_report_can_be_exported_as_excel(): void
    {
        $member = Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $workbook = app(ExportContributionReport::class)->execute(2026, 6, 6);

        $worksheet = $this->worksheetXml($workbook);

        $this->assertStringContainsString('Member Contributions', $worksheet);
        $this->assertStringContainsString('Maria Santos', $worksheet);
        $this->assertStringContainsString('Jun 07', $worksheet);
        $this->assertStringContainsString('Paid', $worksheet);
        $this->assertStringContainsString('<v>10.00</v>', $worksheet);
        $this->assertStringContainsString('state="frozen"', $worksheet);
        $this->assertWorksheetXmlIsLoadable($worksheet);
    }

    public function test_contribution_report_excel_sanitizes_invalid_xml_text(): void
    {
        Member::create([
            'name' => "Ampersand & Control \x01 Member",
            'indigent' => false,
        ]);

        $workbook = app(ExportContributionReport::class)->execute(2026, 6, 6);

        $worksheet = $this->worksheetXml($workbook);

        $this->assertStringContainsString('Ampersand &amp; Control  Member', $worksheet);
        $this->assertStringNotContainsString("\x01", $worksheet);
        $this->assertWorksheetXmlIsLoadable($worksheet);
    }

    public function test_filament_contribution_report_downloads_excel(): void
    {
        Member::create([
            'name' => 'Maria Santos',
            'indigent' => false,
        ]);

        Livewire::actingAs($this->userWithRole(UserRole::Staff))
            ->test(ContributionReport::class)
            ->set('year', 2026)
            ->set('startMonth', 6)
            ->set('endMonth', 6)
            ->call('exportExcel')
            ->assertFileDownloaded('contribution-report-2026-06-06.xlsx');
    }

    public function test_member_cannot_view_filament_contribution_report(): void
    {
        $this->actingAs($this->userWithRole(UserRole::Member))
            ->get('/admin/reports/contributions')
            ->assertForbidden();
    }

    private function worksheetXml(string $workbook): string
    {
        $path = tempnam(sys_get_temp_dir(), 'contribution-report-test-');
        $this->assertNotFalse($path);
        file_put_contents($path, $workbook);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));

        $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);

        $this->assertNotFalse($worksheet);

        return (string) $worksheet;
    }

    private function assertWorksheetXmlIsLoadable(string $worksheet): void
    {
        $document = new \DOMDocument();

        $this->assertTrue($document->loadXML($worksheet));
    }

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role->value,
        ]);
    }
}
