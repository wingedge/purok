# Purok Roadmap

This roadmap separates what is already working in the current app from what still needs to be built or refactored. It should be updated whenever a feature area changes.

## Current Status Summary

Purok currently has a working Laravel MVC foundation with authenticated CRUD screens for members, contributions, finances, inventory, rentals, certificate logs, dashboard summaries, and reports. Filament 4 has been introduced for the back office. Filament now covers members, dependents, contribution record CRUD, the contribution grid, expense CRUD, income CRUD, inventory CRUD, rental CRUD, certificate log CRUD, dashboard summary stats, the filterable dashboard summary page, reports landing, the cash-flow report page, the contribution report page, and CSV import/export operations. The first member-facing portal profile screen is implemented.

## Done

### Foundation

- Laravel application is installed and bootstrapped.
- Breeze-style authentication is present.
- User registration, login, logout, password reset, email verification, password update, and profile update/delete routes exist.
- TailwindCSS, Alpine.js, and Vite are configured.
- Filament 4 is installed.
- Filament admin panel is available at `/admin`.
- A shared Filament admin theme is registered for custom page/form spacing.
- Main authenticated layout and navigation are implemented.
- Basic auth/profile tests exist.

### Users And Roles

- `users` table exists.
- `role` column exists with `admin`, `treasurer`, `staff`, and `member`.
- `member_id` column links users to member records for future member portal accounts.
- `User` model has role helper methods.
- `User` controls Filament panel access through `canAccessPanel`.
- Database seeder creates two admin users.

### Members And Dependents

- `members` table exists.
- `dependents` table exists.
- Member CRUD is implemented.
- Member search is implemented on the member index.
- Dependents can be created and edited through member forms.
- Member detail page loads dependents.
- Legacy member index query logic is extracted to `App\Actions\Members\ListMembers`.
- CSV member/dependent import exists.
- CSV member/dependent import is extracted to `App\Actions\Imports\ImportMembers`.
- CSV member/dependent import has focused feature tests.
- CSV member/dependent export exists.
- CSV member/dependent export is extracted to `App\Actions\Exports\ExportMembers`.
- CSV member/dependent export has focused feature tests.
- Legacy member create and update persistence is extracted to `App\Actions\Members\CreateMember` and `App\Actions\Members\UpdateMember`.
- Legacy member deletion is extracted to `App\Actions\Members\DeleteMember`.
- Member deletion cascades to dependents through database constraints.
- Legacy member create, update, show, delete, dependent replacement, and role boundary behavior has focused feature tests.
- Filament `MemberResource` is implemented for back-office list/create/edit workflows.
- Filament `DependentsRelationManager` is implemented for dependent management from the member edit screen.
- Filament member resource access has focused feature tests.
- Officer records are implemented as member-linked community positions.
- Filament `OfficerResource` is implemented for officer list/create/edit workflows.
- Filament officer resource access has focused feature tests.
- Filament `ExpenseResource` is implemented for back-office finance CRUD.
- Filament `IncomeResource` is implemented for back-office finance CRUD.
- Filament finance resource access has focused feature tests.

### Contributions

- `contributions` table exists.
- Weekly contribution tracking is implemented.
- Contributions are keyed by `member_id` and `week_start`.
- Monthly and yearly contribution views are available.
- Indigent members are excluded from the contribution grid.
- Contributions can be added and removed through JSON endpoints.
- Yearly totals are shown per member.
- Contribution amount and accounting-period date logic are centralized in `ContributionService`.
- Dashboard and cash flow contribution totals use `week_start`.
- Contribution creation/update and deletion are extracted to Actions.
- Contribution rules have focused feature tests.
- Contribution record CRUD is available in Filament.
- The monthly/yearly contribution grid is available in Filament.
- Filament contribution resource access has focused feature tests.
- Filament contribution grid access and toggle behavior have focused feature tests.
- Contribution CSV import/export is implemented through Actions and Filament `DataExchange`.
- Contribution CSV import/export has focused feature tests.

### Finances

- `incomes` table exists.
- `expenses` table exists.
- Income CRUD is implemented.
- Expense CRUD is implemented.
- Income and expense CRUD are also available in Filament.
- Expense records store the creating user.
- Income records can be linked to rentals.
- Income sources and expense categories are centralized in shared support classes.
- Legacy income and expense create, update, and delete persistence is extracted to Actions.
- Legacy income and expense index query logic is extracted to Actions.
- Legacy income and expense create, update, delete, and role boundary behavior has focused feature tests.

### Inventory And Rentals

- `inventories` table exists.
- `rentals` table exists.
- Inventory CRUD is implemented.
- Inventory tracks total quantity, available quantity, and rental rate.
- Rental CRUD is implemented.
- Inventory and rental CRUD are also available in Filament.
- Rental creation decrements available inventory.
- Rental creation creates linked income.
- Rental updates can sync linked income.
- Rental return restores inventory.
- Rental deletion restores inventory when needed and removes linked income.
- Rental/inventory updates use database transactions for the main workflows.
- Rental/inventory/income synchronization is extracted to Actions.
- Rental workflow Actions have focused feature tests.
- Filament logistics resource access has focused feature tests.

### Purok Certificate Logs

- `purok_certificates` table exists.
- Certificate log CRUD is implemented.
- Certificate routes require authenticated and verified users.
- Certificate logs can search by member or dependent name.
- Member search JSON endpoint exists for certificate forms.
- Legacy certificate create, update, delete, search, and list query behavior has focused feature tests.
- Legacy certificate persistence, deletion, member search, and list filtering are extracted to Actions.
- Certificate log CRUD is also available in Filament.
- Filament certificate resource access has focused feature tests.

### Dashboard And Reports

- Authenticated dashboard exists.
- Dashboard summarizes members, income, contributions, expenses, contributors, rentals, and total funds.
- Dashboard supports year and optional month filtering.
- Dashboard summary query logic is extracted to `BuildDashboardSummary`.
- Filament dashboard shows current-year summary stats.
- Filterable dashboard summary is available in Filament.
- Filament dashboard summary access has focused feature tests.
- Reports landing page exists.
- Reports landing page is available in Filament.
- Cash flow report exists.
- Cash flow report totals are extracted to `BuildCashFlowReport`.
- Cash flow report is available in Filament.
- Filament cash-flow report access and totals have focused feature tests.
- Contribution report exists.
- Contribution report totals are extracted to `BuildContributionReport`.
- Contribution report is available in Filament.
- Contribution report totals and Filament access have focused feature tests.
- Contribution report Excel export is available from Filament.
- Contribution report Excel export has focused feature tests.

### Imports And Exports

- Member/dependent CSV import/export is implemented through Actions.
- Expense CSV import/export is implemented through Actions.
- Income CSV import/export is implemented through Actions.
- Contribution CSV import/export is implemented through Actions.
- Inventory CSV import/export is implemented through Actions.
- Rental CSV import/export is implemented through Actions.
- Back-office CSV import/export operations are available in Filament.
- Filament data exchange access, import, and export flows have focused feature tests.
- Old Blade back-office entry pages redirect to Filament.
- Old Blade/controller back-office mutation, import, export, and report-detail routes are no longer publicly accessible.
- Live-site deployment steps are documented in `docs/live-site-deployment.md`.

## Not Done

### Stack Direction

- Laravel 13 is no longer a mandatory upgrade target; keep the current Laravel version unless a clear maintenance or compatibility reason appears.
- PHP 8.3+ is preferred, but the current PHP requirement can remain if there are no compatibility issues.
- Livewire 4 is optional and should only be introduced when a backend or member portal workflow clearly benefits from it.
- Filament 4 is installed and used for the admin panel.
- Filament Resources and pages are implemented for members, dependents, contribution records, the contribution grid, expenses, incomes, inventory, rentals, and certificate logs.
- Back-office dashboard, report, and import/export pages are now available in Filament.
- Old Blade back-office entry points redirect to Filament. Old back-office mutation, import, export, and report-detail compatibility routes have been removed from public access.
- Member self-service screens are currently Blade/controller-based; moving them to Livewire is optional, not required.

### Architecture Refactor

- The first Action extraction exists for member/dependent import.
- Rental workflow Actions exist for create, update, return, and delete.
- Certificate log Actions exist for legacy create, update, delete, member search, and list filtering workflows.
- Member create, update, delete, and list Actions exist for legacy member/dependent workflows.
- Income and expense create, update, and delete Actions exist for legacy finance persistence.
- Member, income, and expense list Actions exist for legacy index query logic.
- Contribution rules are extracted to `ContributionService`.
- Contribution record/create/delete workflows are partially Action-based.
- `ContributionService` is implemented; other service extractions remain pending.
- Repositories are not implemented.
- Import result DTOs are implemented for member/dependent import.
- A `UserRole` enum is implemented.
- Most business workflows still live directly in controllers.
- Most controllers are not yet thin.
- Dashboard summary query logic is extracted to `BuildDashboardSummary`; cash-flow report query logic is extracted to `BuildCashFlowReport`; contribution report query logic is extracted to `BuildContributionReport`.

### Authorization

- Initial role-based route access is implemented with gates and route middleware.
- Filament panel access is restricted to admin, treasurer, and staff users.
- Member, income, expense, inventory, rental, certificate log, contribution, officer, and user model policies are implemented and preserve the current gate-based role rules.
- Reports still use gates and page/route-level checks rather than model policies.
- Staff/treasurer/admin permissions are documented in `docs/features/authorization.md` and partially enforced in code.
- Member-role users are blocked from Filament admin access.
- Member portal permissions are implemented for the first profile/dependent self-service screen.

### Member Portal

- Members can log in as community members with scoped profile access.
- Members can update approved profile fields from a self-service portal.
- Members can manage dependents from a self-service portal.
- Users can be linked to member records.
- The `member` role exists for member-facing accounts.
- Member-role users cannot access the Filament admin panel.
- Member-role users are redirected to `/member/profile` after login.
- Member-role users are blocked from the back-office dashboard.
- Staff/admin can create or update linked member portal accounts from the Filament member edit screen.
- Formal member account invitation/claiming flow is not implemented.
- Member portal read-only contribution status is implemented for the linked member record.
- Member portal contribution history can be filtered by year and optional month.
- Member portal email updates sync to the linked login account.
- Member portal password change and forgot-password flows are available through the linked user account.

### Imports

Target import/export scope from `AGENTS.md`:

- Members and dependents
- Expenses
- Incomes
- Contributions
- Inventory
- Rentals

Current state and gaps:

- Shared import/export architecture is partially implemented through focused Actions.
- Current CSV member/dependent import is action-based.
- Current CSV member/dependent export is action-based.
- Expense import/export is implemented.
- Income import/export is implemented.
- Contribution import/export is implemented.
- Inventory import/export is implemented.
- Rental import/export is implemented.
- Filament import/export operations page is implemented.
- Member/dependent import returns an `ImportResult` summary.
- Expense import validation/reporting is implemented.
- Income import validation/reporting is implemented.
- Rental import validation/reporting is implemented.
- Stable export column definitions are documented in `docs/features/import-export.md`.
- Import file formats are documented in `docs/features/import-export.md`.

### Finance Improvements

- Income and expense category/source enums or lookup tables are not implemented.
- Income and expense category/source values are centralized but still stored as plain strings.
- Contribution amount rules are centralized but not configurable.
- Cash on hand/opening balance workflow is not clearly modeled.

### Inventory And Rental Improvements

- Rental business logic is action-based for create, update, return, and delete.
- Rental pricing is not centralized.
- Rental amount is stored as linked income, not on the rental.
- Inventory adjustment/audit history is not implemented.
- Manual inventory quantity edits are not guarded by a correction workflow.

### Certificate Improvements

- Printable certificate generation is not implemented.
- Certificate logs do not track a specific dependent as the certificate subject.
- Certificate status, release metadata, and issued-by fields are not modeled.

### Reporting

- Contribution report Excel export is implemented.
- Structured exports for other reports are not implemented.
- Browser print/save-PDF support exists for some report views, but it is not the primary contribution report export workflow.
- Contribution report query logic is extracted to `BuildContributionReport`.
- Report filters are basic.
- Dashboard summary, cash-flow report, and contribution report totals have focused tests.
- No printable report layout is documented.

### Testing

- Legacy member CRUD and dependent replacement tests exist.
- Authorization tests exist for the first role-protected route boundaries.
- Filament member resource access tests exist.
- Filament finance resource access tests exist.
- Filament dashboard summary tests exist.
- Filament filterable dashboard summary tests exist.
- Filament reports landing tests exist.
- Filament cash-flow report tests exist.
- Filament data exchange tests exist.
- Member/dependent import tests exist.
- Member/dependent export tests exist.
- Contribution amount and accounting-period tests exist.
- Filament contribution grid tests exist.
- Legacy income and expense CRUD tests exist.
- Rental inventory synchronization tests exist.
- Rental import/export tests exist.
- Contribution import/export tests exist.
- Legacy certificate log create, update, delete, member search, and list filtering tests exist.
- Contribution report total tests exist.
- Contribution report Excel export tests exist.

### Documentation

- Feature-specific docs exist for authorization and import/export.
- Member portal flow is documented in `docs/features/member-portal.md`.
- Role and permission rules are documented.
- Import file formats are documented.
- Report definitions are not documented.

## Suggested Next Steps

1. Decide whether to replace staff-set temporary passwords with email invitations or account claiming tokens.
2. Review whether unreachable old Blade back-office templates and unused controller methods can be deleted after live-site smoke testing.
3. Add more `docs/features/` documents as each feature area is refactored.
