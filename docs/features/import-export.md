# Imports And Exports

This document defines the target import/export contracts for the Purok back-office system. The current application only supports CSV import for members and dependents through `MemberController@import`.

## Current State

- Member/dependent CSV import exists.
- Member/dependent import logic lives in `App\Actions\Imports\ImportMembers`.
- `MemberController@import` validates the uploaded file and delegates to the import action.
- Member/dependent CSV export exists.
- Member/dependent export logic lives in `App\Actions\Exports\ExportMembers`.
- `MemberController@export` delegates to the export action and returns a CSV download.
- Expense import/export is implemented.
- Income import/export is implemented.
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
- `ExpenseController` and Filament `DataExchange` validate uploaded CSV files and delegate import/export work to Actions.
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
- `IncomeController` and Filament `DataExchange` validate uploaded CSV files and delegate import/export work to Actions.
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

## Rentals

Current implementation:

- Rental CSV export lives in `App\Actions\Exports\ExportRentals`.
- Rental CSV import lives in `App\Actions\Imports\ImportRentals`.
- `RentalController` and Filament `DataExchange` validate uploaded CSV files and delegate import/export work to Actions.
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

## Recommended Classes

- `App\Actions\Imports\ImportMembers`
- `App\Actions\Exports\ExportMembers`
- `App\Actions\Imports\ImportExpenses`
- `App\Actions\Exports\ExportExpenses`
- `App\Actions\Imports\ImportIncomes`
- `App\Actions\Exports\ExportIncomes`
- `App\Actions\Imports\ImportRentals`
- `App\Actions\Exports\ExportRentals`
- `App\Data\Imports\ImportResult`
- `App\Data\Imports\FailedImportRow`

## Implementation Order

1. Expense export and import are implemented.
2. Income export and import are implemented.
3. Rental export and import are implemented.
