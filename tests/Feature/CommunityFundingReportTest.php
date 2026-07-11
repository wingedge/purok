<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Exports\ExportCommunityFundingReport;
use App\Actions\Reports\BuildCommunityFundingReport;
use App\Enums\UserRole;
use App\Filament\Pages\CommunityFundingReport;
use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class CommunityFundingReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_is_limited_to_selected_event_and_calculates_summary(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $otherMember = Member::create(['name' => 'Jose Reyes']);
        $event = CommunityFundingEvent::create(['name' => 'Street Lights', 'goal_amount' => 1000]);
        $otherEvent = CommunityFundingEvent::create(['name' => 'Cleanup Drive', 'goal_amount' => 500]);
        CommunityFundingDonation::create(['community_funding_event_id' => $event->id, 'member_id' => $member->id, 'amount' => 300, 'received_at' => '2026-06-01', 'remarks' => 'First donation']);
        CommunityFundingDonation::create(['community_funding_event_id' => $event->id, 'member_id' => $otherMember->id, 'amount' => 200, 'received_at' => '2026-06-02']);
        CommunityFundingDonation::create(['community_funding_event_id' => $otherEvent->id, 'member_id' => $member->id, 'amount' => 400, 'received_at' => '2026-06-03']);

        $report = app(BuildCommunityFundingReport::class)->execute($event->id);

        $this->assertTrue($report['event']->is($event));
        $this->assertCount(2, $report['donations']);
        $this->assertSame(1000.0, $report['goalAmount']);
        $this->assertSame(500.0, $report['collectedAmount']);
        $this->assertSame(500.0, $report['remainingAmount']);
        $this->assertSame(50.0, $report['progressPercentage']);
        $this->assertSame(2, $report['donorCount']);
    }

    public function test_staff_can_select_and_view_community_funding_event_report(): void
    {
        $member = Member::create(['name' => 'Maria Santos']);
        $event = CommunityFundingEvent::create(['name' => 'Street Lights', 'description' => 'Install lights', 'goal_amount' => 1000]);
        CommunityFundingDonation::create(['community_funding_event_id' => $event->id, 'member_id' => $member->id, 'amount' => 300, 'received_at' => '2026-06-01']);
        $staff = User::factory()->create(['role' => UserRole::Staff->value]);

        $this->actingAs($staff)->get('/admin/reports/community-funding?event_id='.$event->id)
            ->assertOk()
            ->assertSee('Community Funding Report')
            ->assertSee('Street Lights')
            ->assertSee('Maria Santos')
            ->assertSee('PHP 300.00');
    }

    public function test_excel_export_contains_only_selected_event_donations(): void
    {
        $member = Member::create(['name' => 'Maria & Santos']);
        $event = CommunityFundingEvent::create(['name' => 'Street Lights', 'goal_amount' => 1000]);
        $otherEvent = CommunityFundingEvent::create(['name' => 'Cleanup Drive', 'goal_amount' => 500]);
        CommunityFundingDonation::create(['community_funding_event_id' => $event->id, 'member_id' => $member->id, 'amount' => 300, 'received_at' => '2026-06-01', 'remarks' => 'Main road']);
        CommunityFundingDonation::create(['community_funding_event_id' => $otherEvent->id, 'member_id' => $member->id, 'amount' => 400, 'received_at' => '2026-06-03', 'remarks' => 'Cleanup only']);

        $worksheet = $this->worksheetXml(app(ExportCommunityFundingReport::class)->execute($event->id));

        $this->assertStringContainsString('Street Lights', $worksheet);
        $this->assertStringContainsString('Maria &amp; Santos', $worksheet);
        $this->assertStringContainsString('Main road', $worksheet);
        $this->assertStringContainsString('<v>300.00</v>', $worksheet);
        $this->assertStringNotContainsString('Cleanup only', $worksheet);
        $this->assertTrue((new \DOMDocument)->loadXML($worksheet));
    }

    public function test_filament_page_downloads_selected_event_excel(): void
    {
        $event = CommunityFundingEvent::create(['name' => 'Street Lights Project', 'goal_amount' => 1000]);
        $staff = User::factory()->create(['role' => UserRole::Staff->value]);

        Livewire::actingAs($staff)->test(CommunityFundingReport::class)
            ->set('eventId', $event->id)
            ->call('exportExcel')
            ->assertFileDownloaded('community-funding-report-street-lights-project.xlsx');
    }

    private function worksheetXml(string $workbook): string
    {
        $path = tempnam(sys_get_temp_dir(), 'community-funding-report-test-');
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
