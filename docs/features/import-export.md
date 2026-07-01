# Imports And Exports

This document defines the target import/export contracts for the Purok back-office system. CSV import/export workflows are now exposed through Filament `DataExchange` and delegated to Actions.

## Current State

- Member/dependent CSV import exists.
- Member/dependent import logic lives in `App\Actions\Imports\ImportMembers`.
- Filament `DataExchange` validates uploaded member CSV files and delegates to the import action.
- Member/dependent CSV export exists.
- Member/dependent export logic lives in `App\Actions\Exports\ExportMembers`.
- Filament `DataExchange` delegates member CSV export work to the export action.
- Expense import/export is implemented.
- Income import/export is implemented.
- Contribution import/export is implemented.
- Community funding event import/export is implemented.
- Community funding donation import/export is implemented.
- Inventory import/export is implemented.
- Rental import/export is implemented.
- Member/dependent import returns an `ImportResult` with created, updated, skipped, and failed counts.
- Failed member/dependent rows include row number, original data, and validation errors.

## General Rules

- CSV is the first supported format.
- Exports should use the same column names accepted by imports wherever practical.
- Files should use UTF-8 encoding.
- Dates should use `YYYY-MM-DD`.
- Currency should use plain decimal values without currency symbols.
- Boolean values should accept `yes`, `no`, `true`, `false`, `1`, and `0` on import.
- Import actions should return a summary with created, updated, skipped, and failed row counts.
- Failed rows should include row number, reason, and original row data.
- Import parsing, validation, persistence, and export generation should live in Actions or Services, not controllers.
- Controllers, Filament actions, or Livewire components should only validate the uploaded file, call the Action or Service, and return the result to the user.

## Members And Dependents

### Current Import Columns

The existing import expects these member columns:

- `name`
- `phone`
- `email`
- `birthday`
- `indigent`
- `dependent_names`

Current dependent format:

- `dependent_names` is a pipe-delimited list, for example `Juan Cruz|Maria Cruz`.
- Dependent relationships are not imported by the current flow.

### Target Import Columns

Required:

- `name`

Optional:

- `phone`
- `email`
- `birthday`
- `indigent`
- `dependent_names`
- `dependent_relationships`

Rules:

- `dependent_names` and `dependent_relationships` should both use pipe-delimited values.
- If relationships are provided, each relationship should match the dependent in the same position.
- Empty dependent names should be ignored.
- Invalid email/date values should fail the row.
- Duplicate behavior must be explicit before implementation. Recommended default: update an existing member only when a stable identifier is provided; otherwise create a new member.

### Target Export Columns

- `id`
- `name`
- `phone`
- `email`
- `birthday`
- `indigent`
- `dependent_names`
- `dependent_relationships`
- `created_at`
- `updated_at`

## Expenses

Current implementation:

- Expense CSV export lives in `App\Actions\Exports\ExportExpenses`.
- Expense CSV import lives in `App\Actions\Imports\ImportExpenses`.
- Filament `DataExchange` validates uploaded CSV files and delegates expense import/export work to Actions.
- Imported expenses record the authenticated user as `created_by`.

### Target Import Columns

Required:

- `date`
- `category`
- `amount`

Optional:

- `description`

Rules:

- `amount` must be zero or greater.
- `category` should eventually validate against an enum or lookup table.
- Imported expenses should record the authenticated user as `created_by`.

### Target Export Columns

- `id`
- `date`
- `category`
- `description`
- `amount`
- `created_by`
- `created_by_name`
- `created_at`
- `updated_at`

## Incomes

Current implementation:

- Income CSV export lives in `App\Actions\Exports\ExportIncomes`.
- Income CSV import lives in `App\Actions\Imports\ImportIncomes`.
- Filament `DataExchange` validates uploaded CSV files and delegates income import/export work to Actions.
- `rental_id` is optional and must reference an existing rental when provided.

### Target Import Columns

Required:

- `date`
- `source`
- `amount`

Optional:

- `description`
- `rental_id`

Rules:

- `amount` must be zero or greater.
- `source` should eventually validate against an enum or lookup table.
- `rental_id` should only be accepted when it references an existing rental.
- Rental-created incomes should normally be managed through rental workflows, not manual income imports.

### Target Export Columns

- `id`
- `date`
- `source`
- `description`
- `amount`
- `rental_id`
- `created_at`
- `updated_at`

## Contributions

Current implementation:

- Contribution CSV export lives in `App\Actions\Exports\ExportContributions`.
- Contribution CSV import lives in `App\Actions\Imports\ImportContributions`.
- Filament `DataExchange` validates uploaded CSV files and delegates contribution import/export work to Actions.
- Imports reuse `RecordContribution` so the contribution amount rule remains centralized in `ContributionService`.
- Existing member/week records are updated; new member/week records are created.

### Target Import Columns

Required:

- `week_start`
- Either `member_id` or `member_name`

Optional:

- `remarks`
- `id`
- `amount`
- `created_at`
- `updated_at`

Rules:

- `week_start` must use `YYYY-MM-DD`.
- `member_id` must reference an existing member when provided.
- `member_name` may be used when it matches exactly one existing member.
- `amount` is not trusted on import; the stored amount is calculated by `ContributionService`.
- Duplicate member/week imports update the existing contribution remarks and recalculated amount.
- Failed rows include row number, original data, and validation errors.

### Target Export Columns

- `id`
- `member_id`
- `member_name`
- `week_start`
- `amount`
- `remarks`
- `created_at`
- `updated_at`

## Community Funding

Current implementation:

- Community funding event CSV export lives in `App\Actions\Exports\ExportCommunityFundingEvents`.
- Community funding event CSV import lives in `App\Actions\Imports\ImportCommunityFundingEvents`.
- Community funding donation CSV export lives in `App\Actions\Exports\ExportCommunityFundingDonations`.
- Community funding donation CSV import lives in `App\Actions\Imports\ImportCommunityFundingDonations`.
- Filament `DataExchange` validates uploaded CSV files and delegates community funding import/export work to Actions.

### Event Import Columns

Required:

- `name`

Optional:

- `id`
- `description`
- `deadline`
- `goal_amount`
- `actual_amount`
- `created_at`
- `updated_at`

Rules:

- `deadline` must use `YYYY-MM-DD` when provided.
- `goal_amount` may be blank. When provided, it must be zero or greater.
- `actual_amount` is ignored on import because actual funding totals are computed from donations.
- Rows with an existing `id` update that event; rows without `id` create a new event.

### Event Export Columns

- `id`
- `name`
- `description`
- `deadline`
- `goal_amount`
- `actual_amount`
- `created_at`
- `updated_at`

### Donation Import Columns

Required:

- `amount`
- `received_at`
- Either `community_funding_event_id` or `community_funding_event_name`
- Either `member_id` or `member_name`

Optional:

- `id`
- `remarks`
- `created_at`
- `updated_at`

Rules:

- `received_at` must use `YYYY-MM-DD`.
- `amount` must be greater than zero.
- Event and member names must each match exactly one record when IDs are not provided.
- Rows with an existing `id` update that donation; rows without `id` create a new donation.

### Donation Export Columns

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

## Rentals

Current implementation:

- Rental CSV export lives in `App\Actions\Exports\ExportRentals`.
- Rental CSV import lives in `App\Actions\Imports\ImportRentals`.
- Filament `DataExchange` validates uploaded CSV files and delegates rental import/export work to Actions.
- Active imported rentals use the rental creation workflow, decrement inventory, and create linked income.
- Returned imported rentals are treated as historical rentals, create linked income, and do not decrement current inventory.

### Target Import Columns

Required:

- `inventory_id`
- `renter_name`
- `renter_contact`
- `quantity`
- `rent_date`
- `amount`

Optional:

- `status`
- `return_date`

Rules:

- `inventory_id` must reference an existing inventory item.
- `quantity` must be at least one.
- Importing active rentals must respect available inventory.
- Importing returned rentals should create a historical rental without reducing current available inventory, unless an explicit stock adjustment mode is introduced.
- Each imported rental should create or sync linked income through the same rental service used by manual rental creation.
- `status` should default to `rented`.
- Allowed statuses are `rented` and `returned`.

### Target Export Columns

- `id`
- `inventory_id`
- `inventory_item_name`
- `renter_name`
- `renter_contact`
- `quantity`
- `rent_date`
- `return_date`
- `status`
- `amount`
- `income_id`
- `created_at`
- `updated_at`

## Inventory

Current implementation:

- Inventory CSV export lives in `App\Actions\Exports\ExportInventories`.
- Inventory CSV import lives in `App\Actions\Imports\ImportInventories`.
- Filament `DataExchange` validates uploaded CSV files and delegates inventory import/export work to Actions.

### Target Import Columns

Required:

- `item_name`
- `total_quantity`

Optional:

- `available_quantity`
- `rental_rate`

Rules:

- `total_quantity` must be zero or greater.
- `available_quantity` must be zero or greater and cannot exceed `total_quantity`.
- Blank `available_quantity` defaults to `total_quantity`.
- Blank `rental_rate` defaults to `0.00`.

### Target Export Columns

- `id`
- `item_name`
- `total_quantity`
- `available_quantity`
- `rental_rate`
- `created_at`
- `updated_at`

## Recommended Classes

- `App\Actions\Imports\ImportMembers`
- `App\Actions\Exports\ExportMembers`
- `App\Actions\Imports\ImportExpenses`
- `App\Actions\Exports\ExportExpenses`
- `App\Actions\Imports\ImportIncomes`
- `App\Actions\Exports\ExportIncomes`
- `App\Actions\Imports\ImportContributions`
- `App\Actions\Exports\ExportContributions`
- `App\Actions\Imports\ImportCommunityFundingEvents`
- `App\Actions\Exports\ExportCommunityFundingEvents`
- `App\Actions\Imports\ImportCommunityFundingDonations`
- `App\Actions\Exports\ExportCommunityFundingDonations`
- `App\Actions\Imports\ImportInventories`
- `App\Actions\Exports\ExportInventories`
- `App\Actions\Imports\ImportRentals`
- `App\Actions\Exports\ExportRentals`
- `App\Data\Imports\ImportResult`
- `App\Data\Imports\FailedImportRow`

## Implementation Order

1. Expense export and import are implemented.
2. Income export and import are implemented.
3. Inventory export and import are implemented.
4. Rental export and import are implemented.
5. Contribution export and import are implemented.
6. Community funding event and donation export/import are implemented.
