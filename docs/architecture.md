# Purok Architecture

This document describes the current structure and request flow of the Purok Laravel application. It is a snapshot of the app as it exists now, plus notes about the target direction from `AGENTS.md`.

## Current Stack

- Laravel 12 application with Laravel Breeze-style authentication.
- PHP requirement in `composer.json`: `^8.2`.
- Blade views with TailwindCSS, Alpine.js, and Vite.
- Eloquent models and migrations for community, finance, certificate, inventory, and rental records.
- Sanctum is installed, but there are no custom API endpoints in active use.

`AGENTS.md` lists the intended target stack as Laravel 13, PHP 8.3+, Livewire 4, Filament 4, TailwindCSS, and MySQL. The current codebase has not yet moved to Livewire or Filament.

## Application Shape

The app currently follows classic Laravel MVC:

- Routes are defined in `routes/web.php` and `routes/auth.php`.
- Controllers validate requests, run Eloquent queries, perform business operations, and return Blade views or JSON responses.
- Models define fillable fields, casts, and relationships.
- Blade templates render forms, tables, dashboards, reports, and navigation.
- The first Action/DTO extraction exists for member imports.
- Services, Repositories, Livewire components, and Filament Resources are not implemented yet.

The main authenticated navigation is grouped into:

- Dashboard
- Community
- Finances
- Logistics
- Reports
- Profile/logout

Feature contracts live under `docs/features/`. Current contracts:

- `docs/features/authorization.md`
- `docs/features/import-export.md`

## Authentication And Users

Authentication is generated from Laravel Breeze patterns:

- Guest routes handle register, login, forgot password, and password reset.
- Authenticated routes handle logout, password confirmation, email verification, password update, and profile management.
- `User` supports a `role` column with `admin`, `treasurer`, and `staff`.
- `User` has convenience methods: `isAdmin()`, `isTreasurer()`, and `isStaff()`.

Current authorization state:

- Roles exist at the model/database level.
- Initial named gates are registered in `AuthServiceProvider`.
- Main route groups and actions use `can:` middleware for admin, treasurer, and staff boundaries.
- Full model policies are not implemented yet.

The target role/permission model is documented in `docs/features/authorization.md`.

## Route Flow

Public routes:

- `/` renders the welcome page.
- Auth routes from `routes/auth.php` handle login, registration, verification, and password reset.

Authenticated routes:

- `/dashboard` uses `DashboardController@index`.
- `/profile` uses `ProfileController`.
- `/members` uses `MemberController` resource routes.
- `/members/import` imports member CSV data.
- `/members/search` returns JSON member search results for certificate flows.
- `/expenses` uses `ExpenseController` resource routes.
- `/incomes` uses `IncomeController` resource routes.
- `/contributions` supports index, store, and destroy.
- `/inventories` uses `InventoryController` resource routes.
- `/rentals` uses `RentalController` resource routes.
- `/rentals/{rental}/return` marks a rental as returned.
- `/reports` renders the reports landing page.
- `/reports/cashflow` uses `Reports\CashFlowController@index`.
- `/reports/contributions` uses `Reports\CashFlowController@contributions`.

Verified authenticated routes:

- `/purok_certificates` uses `PurokCertificateController` resource routes.

## Domain Areas

### Members And Dependents

Models:

- `Member`
- `Dependent`

Relationships:

- A member has many dependents.
- A dependent belongs to a member.
- A member has many contributions.

Flow:

- `MemberController@index` searches members by name, counts dependents, sorts by name, and paginates.
- `create` and `edit` views collect member fields and dependent rows.
- `store` creates a member and any provided dependents.
- `update` updates member data, deletes all existing dependents, then recreates dependents from the submitted form.
- `destroy` deletes the member.
- `import` accepts a CSV file and delegates member/dependent parsing and persistence to `App\Actions\Imports\ImportMembers`.
- `ImportMembers` validates each row, creates members, and optionally creates dependents from pipe-delimited `dependent_names` and `dependent_relationships` fields.

Current concerns:

- Updating dependents by deleting and recreating them is simple, but it does not preserve dependent IDs or history.

### Contributions

Model:

- `Contribution`

Relationships:

- A contribution belongs to a member.
- A member has many contributions.

Flow:

- `ContributionController@index` supports monthly and yearly views.
- The controller generates Sundays within the selected date range.
- Only non-indigent members are listed.
- Contributions for visible weeks are eager-loaded.
- A yearly total is calculated per member with `withSum`.
- `store` creates or updates one contribution for a member/week pair.
- `destroy` removes one member/week contribution.

Business rule:

- The contribution amount is currently hardcoded in the controller as `10.00` for non-indigent members and `0.00` for indigent members.

Current concerns:

- Contribution amount logic belongs in a service or action.
- Dashboard and reports currently filter contribution totals by `created_at`, while the contribution grid uses `week_start`. This should be reviewed so financial reports use the intended accounting date.

### Income And Expenses

Models:

- `Income`
- `Expense`

Relationships:

- An expense belongs to the user who created it through `created_by`.
- An income may belong to a rental through `rental_id`.

Flow:

- `IncomeController` provides CRUD screens for income records.
- Income sources are currently a private array in the controller.
- `ExpenseController` provides CRUD screens for expense records.
- Expense categories are currently a private array in the controller.
- Expenses store the authenticated user's ID in `created_by`.

Current concerns:

- Source and category values should eventually move to enums, configuration, lookup tables, or another explicit domain structure.
- Finance controllers directly contain validation and persistence logic.
- Import/export workflows for incomes and expenses are not implemented yet.

### Inventory And Rentals

Models:

- `Inventory`
- `Rental`
- `Income`

Relationships:

- A rental belongs to an inventory item.
- A rental has one income record.
- An income belongs to a rental.

Flow:

- `InventoryController` manages inventory item CRUD.
- Inventory items track `total_quantity`, `available_quantity`, and `rental_rate`.
- `RentalController@store` validates rental quantity against available inventory.
- Rental creation runs inside a database transaction:
  - Lock inventory row.
  - Create the rental.
  - Create a linked income record.
  - Decrement available inventory quantity.
- `RentalController@update` runs inside a transaction:
  - Locks inventory.
  - Adjusts inventory when the quantity changes.
  - Restores inventory when status changes from rented to returned.
  - Updates or creates the linked income record.
  - Updates the rental.
- `RentalController@destroy` restores inventory for active rentals, deletes the linked income, then deletes the rental.
- `RentalController@returnItem` marks a rental as returned and restores inventory.

Current concerns:

- Rental/inventory/income synchronization is important business logic and should move into an Action or Service.
- `Rental` validates an `amount`, but `amount` is not fillable or stored on the rental itself; it is stored as a linked income record.
- Inventory edits allow directly changing `available_quantity`, so staff can manually correct stock but can also bypass rental rules.
- Rental import/export workflows are not implemented yet.

### Purok Certificate Log

Model:

- `PurokCertificate`

Relationships:

- A certificate log belongs to a member.

Flow:

- Certificate routes require authenticated and verified users.
- `PurokCertificateController@index` searches logs by member name or dependent name.
- `searchMembers` provides JSON autocomplete-style search over members and dependents.
- `store` and `update` validate `member_id`, `request_date`, and `purpose`.

Current concerns:

- The feature logs certificate requests/releases but does not generate printable certificates yet.
- Searching dependents returns the owning member ID, so the certificate record is tied to the member rather than a specific dependent.

### Dashboard And Reports

Controllers:

- `DashboardController`
- `Reports\CashFlowController`

Flow:

- Dashboard summarizes members, incomes, contributions, expenses, contributors, rentals, and total funds.
- Dashboard supports `year` and optional `month` filters.
- Cash flow report totals incomes, contributions, expenses, and net cash flow.
- Contributions report generates weekly columns and lists member contributions over a selected range.

Current concerns:

- Reporting logic is in controllers.
- Contribution report totals use `created_at` in some places and `week_start` in others.
- Browser print styles exist for some reports, but structured exports are not implemented yet.

### Imports And Exports

The target import/export scope from `AGENTS.md` is Purok-specific:

- Members and dependents
- Expenses
- Incomes
- Rentals

Current state:

- Member/dependent CSV import exists in `App\Actions\Imports\ImportMembers`.
- `MemberController@import` validates the uploaded file and delegates to the import action.
- Member/dependent CSV export exists in `App\Actions\Exports\ExportMembers`.
- `MemberController@export` delegates to the export action and returns a CSV download.
- Expense import/export is not implemented.
- Income import/export is not implemented.
- Rental import/export is not implemented.

Recommended architecture:

- Keep import/export parsing, validation, persistence, and file generation outside controllers.
- Use focused Actions or Services such as `ImportMembers`, `ExportMembers`, `ImportExpenses`, `ExportExpenses`, `ImportIncomes`, `ExportIncomes`, `ImportRentals`, and `ExportRentals`.
- Use DTOs or validated row objects for imported rows so controllers, Livewire components, or Filament actions do not pass raw arrays deep into business logic.
- Treat exports as query-backed reports with explicit columns and stable formats.
- Record import results with created, updated, skipped, and failed row counts before expanding imports beyond members.

Detailed import/export columns and rules are documented in `docs/features/import-export.md`.

## Database Overview

Core tables:

- `users`: authentication users plus `role`.
- `members`: community members with contact details, birthday, and indigent flag.
- `dependents`: dependents attached to members.
- `contributions`: member contributions with `week_start`, `amount`, and optional remarks.
- `expenses`: dated expense records with category, description, amount, and creator.
- `incomes`: dated income records with source, description, amount, and optional rental link.
- `inventories`: rentable or tracked items with total and available quantity plus rental rate.
- `rentals`: rental records with inventory, renter details, quantity, dates, and status.
- `purok_certificates`: certificate log entries tied to members.

System tables:

- `cache`, `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`
- `password_reset_tokens`
- `sessions`

Important constraints:

- Dependents cascade when a member is deleted.
- Contributions cascade when a member is deleted.
- Rentals cascade when inventory is deleted.
- Purok certificates cascade when a member is deleted.
- Incomes null out `rental_id` when a rental is deleted, although the rental controller currently deletes linked income manually.
- Contributions are unique by `member_id` and `week_start`.

## Testing

Current tests are mostly Breeze-generated authentication and profile tests:

- Authentication
- Registration
- Password reset/update/confirmation
- Email verification
- Profile update/delete
- Example unit and feature tests

There are no dedicated tests yet for:

- Members and dependents
- Contributions
- Income and expense import/export
- Inventory and rental transactions
- Rental import/export
- Certificate logs
- Reports/dashboard totals

## Architectural Gaps To Address

The biggest gap is that the app's business workflows currently live in controllers. The target architecture in `AGENTS.md` asks for:

- Actions
- Services
- Repositories
- Support classes
- Immutable DTOs where useful
- Enums instead of hardcoded strings
- Thin Livewire components and Filament Resources after the stack migration

Recommended extraction candidates:

- `ImportExpenses`
- `ExportExpenses`
- `ImportIncomes`
- `ExportIncomes`
- `ImportRentals`
- `ExportRentals`
- `RecordContribution`
- `DeleteContribution`
- `CalculateContributionAmount`
- `CreateRentalWithIncome`
- `UpdateRentalWithIncome`
- `ReturnRentalInventory`
- `DeleteRentalAndRestoreInventory`
- `BuildDashboardSummary`
- `BuildCashFlowReport`
- `BuildContributionReport`

These should be introduced incrementally, with focused tests around each moved workflow.
