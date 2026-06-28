# Purok Architecture

This document describes the current structure and request flow of the Purok Laravel application. It is a snapshot of the app as it exists now, plus notes about the target direction from `AGENTS.md`.

## Current Stack

- Laravel 12 application with Laravel Breeze-style authentication.
- PHP requirement in `composer.json`: `^8.2`.
- Filament 4 is installed and exposes a back-office panel at `/admin`.
- Livewire 3 is installed as part of the Filament 4 dependency tree.
- Blade views with TailwindCSS, Alpine.js, and Vite.
- Eloquent models and migrations for community, finance, certificate, inventory, and rental records.
- Sanctum is installed, but there are no custom API endpoints in active use.

`AGENTS.md` now treats the current Laravel version as acceptable unless a clear maintenance or compatibility reason appears. PHP 8.3+ is preferred, but the current PHP requirement can remain if it is not causing issues. Livewire is optional and should only be introduced when a backend or member portal workflow clearly benefits from it. Filament 4 remains the primary back-office direction.

## Application Shape

The app currently follows classic Laravel MVC:

- Routes are defined in `routes/web.php` and `routes/auth.php`.
- Controllers validate requests, run Eloquent queries, perform business operations, and return Blade views or JSON responses.
- Models define fillable fields, casts, and relationships.
- Blade templates render forms, tables, dashboards, reports, and navigation.
- The first Action/DTO extraction exists for member imports.
- Filament Resources and dashboard widgets exist for several back-office workflows.
- Custom Filament page layout helpers live in `public/css/filament/admin/theme.css` and are registered as an additional admin panel CSS asset.
- Services are starting to be introduced for domain rules.
- Repositories are not implemented yet, and member-facing screens currently remain Blade/controller-based.

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
- `docs/features/member-portal.md`
- `docs/features/officers.md`

## Authentication And Users

Authentication is generated from Laravel Breeze patterns:

- Guest routes handle register, login, forgot password, and password reset.
- Authenticated routes handle logout, password confirmation, email verification, password update, and profile management.
- `User` supports a `role` column with `admin`, `treasurer`, `staff`, and `member`.
- `User` may be linked to a `Member` through `member_id`.
- `User` has convenience methods: `isAdmin()`, `isTreasurer()`, `isStaff()`, and `isMember()`.
- `User` implements Filament panel access so only admin, treasurer, and staff can enter `/admin`.
- Member-role users are redirected to `/member/profile` after login.
- Member-role users are blocked from the back-office dashboard.
- Staff/admin can create or update a linked member-role user account from the Filament member edit page.
- Member-role users can view read-only contribution status for their linked member record.
- Member portal email updates sync to the linked user account so login and password reset use the same email.
- Member portal users can change their own password through the existing authenticated password update route.

Current authorization state:

- Roles exist at the model/database level.
- User/member account linking exists through `users.member_id`.
- Initial named gates are registered in `AuthServiceProvider`.
- Main route groups and actions use `can:` middleware for admin, treasurer, and staff boundaries.
- Filament panel access blocks member-role users from the back-office panel.
- Full model policies are not implemented yet.

The target role/permission model is documented in `docs/features/authorization.md`.

## Route Flow

Public routes:

- `/` renders the welcome page.
- Auth routes from `routes/auth.php` handle login, registration, verification, and password reset.

Filament routes:

- `/admin` serves the Filament back-office panel.
- `/admin` includes `App\Filament\Widgets\DashboardStatsOverview` for current-year summary stats.
- `/admin/dashboard-summary` uses `App\Filament\Pages\DashboardSummary`.
- `/admin/reports` uses `App\Filament\Pages\Reports`.
- `/admin/members` uses `App\Filament\Resources\Members\MemberResource`.
- `/admin/members/{record}/edit` allows member edits and dependent management through a relation manager.
- `/admin/officers` uses `App\Filament\Resources\Officers\OfficerResource`.
- `/admin/expenses` uses `App\Filament\Resources\Expenses\ExpenseResource`.
- `/admin/incomes` uses `App\Filament\Resources\Incomes\IncomeResource`.
- `/admin/inventories` uses `App\Filament\Resources\Inventories\InventoryResource`.
- `/admin/rentals` uses `App\Filament\Resources\Rentals\RentalResource`.
- `/admin/purok-certificates` uses `App\Filament\Resources\PurokCertificates\PurokCertificateResource`.
- `/admin/contributions` uses `App\Filament\Resources\Contributions\ContributionResource`.
- `/admin/contribution-grid` uses `App\Filament\Pages\ContributionGrid`.
- `/admin/data-exchange` uses `App\Filament\Pages\DataExchange`.
- `/admin/reports/cash-flow` uses `App\Filament\Pages\CashFlowReport`.
- `/admin/reports/contributions` uses `App\Filament\Pages\ContributionReport`.

Custom Filament page views should use the shared `purok-fi-*` CSS helpers for filter bars, form controls, and action spacing instead of page-local spacing styles.

Authenticated routes:

- `/dashboard` still uses `DashboardController@index` for compatibility.
- `/profile` uses `ProfileController`.
- `/member/profile` uses `MemberPortalController` for member self-service profile and dependent updates.
- Old back-office GET entry pages such as `/members`, `/expenses`, `/incomes`, `/inventories`, `/rentals`, `/contributions`, `/purok_certificates`, and `/reports` redirect to Filament.
- `/members/import` imports member CSV data.
- `/members/search` returns JSON member search results for certificate flows.
- Legacy write, import, export, and report-detail routes remain available while live-site compatibility is verified.
- `/rentals/{rental}/return` marks a rental as returned.
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
- A member may have one linked user account.

Flow:

- `MemberController@index` searches members by name, counts dependents, sorts by name, and paginates.
- `create` and `edit` views collect member fields and dependent rows.
- `store` creates a member and any provided dependents.
- `update` updates member data, deletes all existing dependents, then recreates dependents from the submitted form.
- `destroy` deletes the member.
- Legacy member create, update, show, delete, dependent replacement, and role boundary behavior has focused feature tests.
- `import` accepts a CSV file and delegates member/dependent parsing and persistence to `App\Actions\Imports\ImportMembers`.
- `ImportMembers` validates each row, creates members, and optionally creates dependents from pipe-delimited `dependent_names` and `dependent_relationships` fields.
- `MemberResource` provides the first Filament back-office resource for listing, creating, and editing members.
- `DependentsRelationManager` manages dependents from the Filament member edit screen.
- `OfficerResource` manages member-linked purok officer positions separately from login roles.
- `ExpenseResource` provides Filament back-office CRUD for expense records.
- `IncomeResource` provides Filament back-office CRUD for income records.
- `InventoryResource` provides Filament back-office CRUD for inventory records.
- `RentalResource` provides Filament back-office CRUD for rental records.
- `PurokCertificateResource` provides Filament back-office CRUD for certificate logs.
- `ContributionResource` provides Filament back-office CRUD for individual contribution records.
- `MemberPortalController` allows member-role users linked through `users.member_id` to update their own phone, email, birthday, and dependents.
- `UpdateMemberProfile` and `SyncMemberDependents` keep member self-service persistence outside the controller.
- `CreateMemberPortalAccount` creates or updates a member-role user account for the selected member from the Filament member edit page.
- `BuildMemberContributionStatus` builds the member portal's own-record contribution summary and filtered full history using `ContributionService`.

Current concerns:

- Updating dependents by deleting and recreating them is simple, but it does not preserve dependent IDs or history.
- Member portal dependent updates also replace the full dependent list and do not preserve dependent IDs.

### Contributions

Model:

- `Contribution`

Relationships:

- A contribution belongs to a member.
- A member has many contributions.

Flow:

- `ContributionController@index` supports monthly and yearly views.
- `ContributionService` generates Sundays within the selected date range.
- Only non-indigent members are listed.
- Contributions for visible weeks are eager-loaded.
- A yearly total is calculated per member with `withSum`.
- `store` creates or updates one contribution for a member/week pair.
- `destroy` removes one member/week contribution.
- `RecordContribution` centralizes creating/updating a contribution with the correct service-calculated amount.
- `DeleteContribution` centralizes deleting a contribution by member and week while returning the removed amount.
- `ContributionResource` uses `RecordContribution` for Filament contribution record creation and updates.
- `BuildContributionGrid` centralizes the monthly/yearly grid date range, non-indigent member query, visible contribution eager loading, and yearly total calculation.
- `ContributionGrid` provides the Filament operational grid and reuses `RecordContribution` when toggling payments on and `DeleteContribution` when toggling payments off.

Business rule:

- `ContributionService` calculates the contribution amount as `10.00` for non-indigent members and `0.00` for indigent members.
- Dashboard and cash flow accounting totals use `week_start`.
- Dashboard recent contribution activity still uses `created_at`.

Current concerns:

- Contribution amount is still a fixed rule and is not configurable yet.
- The old Blade contribution grid still exists temporarily while the Filament operational grid is verified.

### Income And Expenses

Models:

- `Income`
- `Expense`

Relationships:

- An expense belongs to the user who created it through `created_by`.
- An income may belong to a rental through `rental_id`.

Flow:

- `IncomeController` provides CRUD screens for income records.
- Income sources are centralized in `IncomeSources`.
- `ExpenseController` provides CRUD screens for expense records.
- Expense categories are centralized in `ExpenseCategories`.
- Expenses store the authenticated user's ID in `created_by`.
- `IncomeResource` and `ExpenseResource` provide Filament CRUD screens for finance records.
- `IncomeSources` and `ExpenseCategories` provide shared option lists for old Blade controllers and Filament forms.
- Legacy income and expense create, update, delete, and role boundary behavior has focused feature tests.

Current concerns:

- Source and category values should eventually move to enums, configuration, lookup tables, or another explicit domain structure.
- Finance controllers still serve old Blade CRUD and import/export routes for compatibility while Filament parity is verified.
- Income and expense source/category options are centralized in support classes, but they are still stored as plain strings.

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
- `RentalController` delegates rental business workflows to Actions.
- `RentalResource` delegates Filament rental create, update, delete, and return workflows to the same rental Actions.
- `CreateRental` runs inside a database transaction:
  - Lock inventory row.
  - Create the rental.
  - Create a linked income record.
  - Decrement available inventory quantity.
- `UpdateRental` runs inside a transaction:
  - Locks inventory.
  - Adjusts inventory when the quantity changes.
  - Restores inventory when status changes from rented to returned.
  - Updates or creates the linked income record.
  - Updates the rental.
- `DeleteRental` restores inventory for active rentals, deletes the linked income, then deletes the rental.
- `ReturnRental` marks a rental as returned and restores inventory once.

Current concerns:

- `Rental` validates an `amount`, but `amount` is not fillable or stored on the rental itself; it is stored as a linked income record.
- Inventory edits allow directly changing `available_quantity`, so staff can manually correct stock but can also bypass rental rules.
- Rental CSV import/export workflows are implemented.

### Purok Certificate Log

Model:

- `PurokCertificate`

Relationships:

- A certificate log belongs to a member.

Flow:

- Certificate routes require authenticated and verified users.
- `PurokCertificateController@index` searches logs by member name or dependent name.
- `searchMembers` provides JSON autocomplete-style search over members and dependents.
- `store` and `update` validate `member_id`, `request_date`, and `purpose`, then delegate persistence to certificate Actions.
- Certificate list filtering, member/dependent search, creation, update, and deletion are extracted to `App\Actions\Certificates`.
- `PurokCertificateResource` provides Filament CRUD with searchable member selection.

Current concerns:

- The feature logs certificate requests/releases but does not generate printable certificates yet.
- Searching dependents returns the owning member ID, so the certificate record is tied to the member rather than a specific dependent.

### Dashboard And Reports

Controllers:

- `DashboardController`
- `Reports\CashFlowController`

Action:

- `BuildDashboardSummary`
- `BuildCashFlowReport`
- `BuildContributionReport`

Flow:

- Dashboard summarizes members, incomes, contributions, expenses, contributors, rentals, and total funds.
- Dashboard supports `year` and optional `month` filters.
- `DashboardController` delegates dashboard totals to `BuildDashboardSummary`.
- `DashboardStatsOverview` reuses `BuildDashboardSummary` for the Filament dashboard's current-year stats.
- `DashboardSummary` reuses `BuildDashboardSummary` for a filterable Filament dashboard summary page.
- `Reports` provides a Filament reports landing page and shows only report links allowed for the current user.
- Cash flow report totals incomes, contributions, expenses, and net cash flow.
- `Reports\CashFlowController@index` delegates cash-flow totals to `BuildCashFlowReport`.
- `CashFlowReport` reuses `BuildCashFlowReport` for the Filament cash-flow report page.
- Contributions report generates weekly columns and lists member contributions over a selected range.
- `Reports\CashFlowController@contributions` delegates contribution report totals to `BuildContributionReport`.
- `ContributionReport` reuses `BuildContributionReport` for the Filament contribution report page.

Current concerns:

- The old dashboard Blade filter view still exists temporarily while the Filament dashboard summary page is verified.
- Browser print styles exist for some reports, but structured exports are not implemented yet.

### Imports And Exports

The target import/export scope from `AGENTS.md` is Purok-specific:

- Classic Blade member CRUD
- Expenses
- Incomes
- Rentals

Current state:

- Member/dependent CSV import exists in `App\Actions\Imports\ImportMembers`.
- `MemberController@import` validates the uploaded file and delegates to the import action.
- Member/dependent CSV export exists in `App\Actions\Exports\ExportMembers`.
- `MemberController@export` delegates to the export action and returns a CSV download.
- Expense CSV import exists in `App\Actions\Imports\ImportExpenses`.
- Expense CSV export exists in `App\Actions\Exports\ExportExpenses`.
- Income CSV import exists in `App\Actions\Imports\ImportIncomes`.
- Income CSV export exists in `App\Actions\Exports\ExportIncomes`.
- Rental CSV import exists in `App\Actions\Imports\ImportRentals`.
- Rental CSV export exists in `App\Actions\Exports\ExportRentals`.
- `DataExchange` provides the Filament CSV import/export page and delegates to the same Actions.

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
- `users.member_id`: optional link to a member record for future member portal accounts.
- `members`: community members with contact details, birthday, and indigent flag.
- `dependents`: dependents attached to members.
- `officers`: purok officer positions linked to members.
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

Current tests include Breeze-generated authentication/profile coverage plus focused tests around the refactored workflows:

- Authentication
- Registration
- Password reset/update/confirmation
- Email verification
- Profile update/delete
- Example unit and feature tests
- Role-based route authorization
- Member/dependent import and export
- Contribution rules and accounting-period totals
- Rental create/update/return/delete workflows
- User-to-member account linking
- Filament member resource access
- Filament officer resource access
- Filament contribution grid access and toggle behavior
- Filament dashboard summary access and totals
- Filament filterable dashboard summary access and totals
- Filament reports landing access
- Filament cash-flow report access and totals
- Contribution report totals and Filament access
- Filament data exchange access, import, and export behavior

The most important legacy CRUD workflows now have focused tests; remaining testing should be added as new refactors or behavior changes are introduced.

## Architectural Gaps To Address

The biggest gap is that the app's business workflows currently live in controllers. The target architecture in `AGENTS.md` asks for:

- Actions
- Services
- Repositories
- Support classes
- Immutable DTOs where useful
- Enums instead of hardcoded strings
- Thin Livewire components only when Livewire is intentionally introduced for a workflow
- Additional Filament Resources as the back-office migration continues

Recommended extraction candidates should be introduced incrementally, with focused tests around each moved workflow.
