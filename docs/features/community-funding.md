# Community Funding

This document defines the community-funding flow.

## Goal

Admins will be able to create community funding events. Each funding event records the name, description or purpose, deadline, and goal amount.

Inside each community funding event, users can record donations from members. Each donation records the member, amount, date received, and optional remarks or comments.

Community funding will be included in report computation as part of cash inflow.

## Actual Amount Decision

The actual amount must be computed from donation records.

Do not store or manually edit `actual_amount` on the funding event. The displayed actual amount should be calculated as:

```text
sum(community_funding_donations.amount)
```

This keeps the total auditable and prevents the event total from drifting away from the recorded donations.

## Data Model

### Community Funding Events

Recommended table: `community_funding_events`

Columns:

- `id`
- `name`
- `description`
- `deadline`
- `goal_amount`
- `created_at`
- `updated_at`

Rules:

- `name` is required.
- `goal_amount` is optional. When provided, it must be zero or greater.
- `deadline` is optional unless the workflow later requires every funding drive to have a deadline.
- Actual amount is computed from donations, not stored.

### Community Funding Donations

Recommended table: `community_funding_donations`

Columns:

- `id`
- `community_funding_event_id`
- `member_id`
- `amount`
- `received_at`
- `remarks`
- `created_at`
- `updated_at`

Rules:

- `community_funding_event_id` is required and must reference an existing funding event.
- `member_id` is required and must reference an existing member.
- `amount` is required and must be greater than zero.
- `received_at` is required.
- `remarks` is optional.

## Relationships

- A community funding event has many donations.
- A community funding donation belongs to one funding event.
- A community funding donation belongs to one member.
- A member has many community funding donations.

## Business Actions

Create focused Actions for the workflow:

- `CreateCommunityFundingEvent`
- `UpdateCommunityFundingEvent`
- `DeleteCommunityFundingEvent`
- `RecordCommunityFundingDonation`
- `UpdateCommunityFundingDonation`
- `DeleteCommunityFundingDonation`

Optional reporting/query Actions:

- `BuildCommunityFundingSummary`
- `BuildCommunityFundingReport`

## Filament UI

Add a Filament resource for funding events:

- `CommunityFundingEventResource`

Event form fields:

- Name
- Description or purpose
- Deadline
- Goal amount, optional

Event table columns:

- Name
- Deadline
- Goal amount
- Actual amount, computed from donations
- Progress percentage
- Status, if useful

Recommended statuses:

- `Active`
- `Completed`
- `Overdue`

Use a RelationManager for event donations:

- Member
- Amount
- Date received
- Remarks

## Authorization

Recommended permissions:

- Admin users can manage community funding events and donations.
- Treasurer users can manage community funding events and donations.
- Staff users should not manage funding by default unless explicitly approved later.
- Member users cannot access back-office community funding screens.

Add or update gates/policies for:

- `view-community-funding`
- `manage-community-funding`

## Reporting

Community funding must be included in cash inflow.

Update cash-flow report logic:

- Add `communityFundingTotal`.
- Add community funding totals to `totalInflow`.
- Include community funding in `netCashFlow`.

Update cash-flow report UI:

- Add a `Community Funding` row under inflows.
- Keep income, member contributions, and community funding visually separate.

Dashboard behavior:

- If dashboard total funds represents available cash, include community funding in total inflow.
- Add a dashboard stat only if it helps operational users; otherwise keep it inside cash-flow reporting.

## Imports And Exports

Import/export can be added after the core workflow is stable.

Recommended exports:

- Community funding events CSV
- Community funding donations CSV

Recommended event export columns:

- `id`
- `name`
- `description`
- `deadline`
- `goal_amount`
- `actual_amount`
- `created_at`
- `updated_at`

Recommended donation export columns:

- `id`
- `community_funding_event_id`
- `community_funding_event_name`
- `member_id`
- `member_name`
- `amount`
- `received_at`
- `remarks`
- `created_at`
- `updated_at`

## Tests

Add focused tests for:

- Event creation, update, and deletion Actions.
- Donation record, update, and delete Actions.
- Actual amount calculation from donations.
- Filament resource access.
- Donation RelationManager behavior.
- Cash-flow report includes community funding as cash inflow.
- Dashboard totals include community funding if dashboard funds are updated.
- Authorization boundaries for admin, treasurer, staff, and member users.

## Implementation Order

1. Create migrations for funding events and donations.
2. Create models and relationships.
3. Add business Actions for event and donation workflows.
4. Add Filament funding event resource.
5. Add donations RelationManager.
6. Add authorization gates and policies.
7. Update cash-flow report computation and UI.
8. Update dashboard totals if total funds should include community funding.
9. Add focused tests.
10. Update `docs/context.md`, `docs/architecture.md`, `docs/roadmap.md`, and `docs/features/authorization.md`.
11. Add import/export later if needed.
