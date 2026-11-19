<?php

namespace Tests\Feature;

use App\Actions\Contributions\DeleteContribution;
use App\Actions\Contributions\RecordContribution;
use App\Actions\Contributions\RecordContributionPayment;
use App\Actions\Dashboard\BuildDashboardSummary;
use App\Actions\Reports\BuildCashFlowReport;
use App\Models\Contribution;
use App\Models\Member;
use App\Services\ContributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_contribution_amount_depends_on_member_indigent_status(): void
    {
        $service = app(ContributionService::class);

        $regularMember = Member::create([
            'name' => 'Regular Member',
            'indigent' => false,
        ]);

        $indigentMember = Member::create([
            'name' => 'Indigent Member',
            'indigent' => true,
        ]);

        $this->assertSame(10.00, $service->amountFor($regularMember));
        $this->assertSame(0.00, $service->amountFor($indigentMember));
    }

    public function test_recording_contribution_uses_service_amount_rule(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        $contribution = app(RecordContribution::class)->execute($member, '2026-06-07');

        $this->assertSame(10.0, (float) $contribution->amount);

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);
    }

    public function test_recording_a_payment_creates_one_paid_week_for_each_weekly_amount(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        $contributions = app(RecordContributionPayment::class)->execute(
            $member,
            2026,
            100,
            'Advance payment',
        );

        $this->assertCount(10, $contributions);
        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-01-04',
            'amount' => 10,
            'remarks' => 'Advance payment',
        ]);
        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-03-08',
            'amount' => 10,
            'remarks' => 'Advance payment',
        ]);
    }

    public function test_later_payments_fill_the_earliest_unpaid_sundays_without_gaps(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);
        foreach (['2026-01-04', '2026-01-11', '2026-01-18', '2026-01-25'] as $weekStart) {
            Contribution::create([
                'member_id' => $member->id,
                'week_start' => $weekStart,
                'amount' => 10,
            ]);
        }

        $contributions = app(RecordContributionPayment::class)->execute($member, 2026, 40);

        $this->assertCount(4, $contributions);
        $this->assertSame('2026-02-01', $contributions->first()->week_start->toDateString());
        $this->assertSame('2026-02-22', $contributions->last()->week_start->toDateString());
        $this->assertDatabaseCount('contributions', 8);
    }

    public function test_recording_a_payment_cannot_exceed_the_remaining_year_balance(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        $summary = app(RecordContributionPayment::class)->summary($member, 2026);
        $overpayment = $summary['remaining_balance'] + 10;

        $this->expectExceptionMessage(sprintf(
            'The payment cannot exceed the remaining balance of PHP %.2f.',
            $summary['remaining_balance'],
        ));

        app(RecordContributionPayment::class)->execute($member, 2026, $overpayment);
    }

    public function test_delete_contribution_action_returns_deleted_amount(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $amount = app(DeleteContribution::class)->execute($member->id, '2026-06-07');

        $this->assertSame(10.0, $amount);
        $this->assertDatabaseMissing('contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
        ]);
    }

    public function test_old_contribution_mutation_routes_are_not_available(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
            'amount' => 10,
        ]);

        $this->deleteJson('/contributions', [
            'member_id' => $member->id,
            'week_start' => '2026-06-07',
        ])
            ->assertMethodNotAllowed();
    }

    public function test_delete_contribution_action_returns_null_for_missing_record(): void
    {
        $member = Member::create([
            'name' => 'Contributor',
            'indigent' => false,
        ]);

        $amount = app(DeleteContribution::class)->execute($member->id, '2026-06-07');

        $this->assertNull($amount);
    }

    public function test_dashboard_contribution_totals_use_week_start_for_accounting_period(): void
    {
        $member = Member::create(['name' => 'Contributor']);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2025-12-28',
            'amount' => 10,
            'created_at' => '2026-01-15 12:00:00',
            'updated_at' => '2026-01-15 12:00:00',
        ]);

        $summary = app(BuildDashboardSummary::class)->execute(2025);

        $this->assertSame(10.0, $summary['totalContributions']);
        $this->assertSame(10.0, $summary['thisYearContributions']);
        $this->assertSame(1, $summary['contributorsCount']);
    }

    public function test_cashflow_contribution_total_uses_week_start_for_accounting_period(): void
    {
        $member = Member::create(['name' => 'Contributor']);
        Contribution::create([
            'member_id' => $member->id,
            'week_start' => '2025-02-02',
            'amount' => 10,
            'created_at' => '2026-01-15 12:00:00',
            'updated_at' => '2026-01-15 12:00:00',
        ]);

        $report = app(BuildCashFlowReport::class)->execute(2025, 2);

        $this->assertSame(10.0, $report['contributionTotal']);
        $this->assertSame(10.0, $report['netCashFlow']);
    }
}
