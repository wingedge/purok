# Purok Roadmap

This roadmap separates what is already working in the current app from what still needs to be built or refactored. It should be updated whenever a feature area changes.

## Current Status Summary

Purok currently has a working Laravel MVC foundation with authenticated CRUD screens for members, contributions, finances, inventory, rentals, certificate logs, dashboard summaries, and reports. Filament 4 has been introduced for the back office. Filament now covers members, dependents, contribution record CRUD, the contribution grid, expense CRUD, income CRUD, inventory CRUD, rental CRUD, certificate log CRUD, dashboard summary stats, the cash-flow report page, and the contribution report page. The first member-facing portal profile screen is implemented.

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
- CSV member/dependent import exists.
- CSV member/dependent import is extracted to `App\Actions\Imports\ImportMembers`.
- CSV member/dependent import has focused feature tests.
- CSV member/dependent export exists.
- CSV member/dependent export is extracted to `App\Actions\Exports\ExportMembers`.
- CSV member/dependent export has focused feature tests.
- Member deletion cascades to dependents through database constraints.
- Filament `MemberResource` is implemented for back-office list/create/edit workflows.
- Filament `DependentsRelationManager` is implemented for dependent management from the member edit screen.
- Filament member resource access has focused feature tests.
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
- Contribution rules have focused feature tests.
- Contribution record CRUD is available in Filament.
- The monthly/yearly contribution grid is available in Filament.
- Filament contribution resource access has focused feature tests.
- Filament contribution grid access and toggle behavior have focused feature tests.

### Finances

- `incomes` table exists.
- `expenses` table exists.
- Income CRUD is implemented.
- Expense CRUD is implemented.
- Income and expense CRUD are also available in Filament.
- Expense records store the creating user.
- Income records can be linked to rentals.

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
- Certificate log CRUD is also available in Filament.
- Filament certificate resource access has focused feature tests.

### Dashboard And Reports

- Authenticated dashboard exists.
- Dashboard summarizes members, income, contributions, expenses, contributors, rentals, and total funds.
- Dashboard supports year and optional month filtering.
- Dashboard summary query logic is extracted to `BuildDashboardSummary`.
- Filament dashboard shows current-year summary stats.
- Filament dashboard summary access has focused feature tests.
- Reports landing page exists.
- Cash flow report exists.
- Cash flow report totals are extracted to `BuildCashFlowReport`.
- Cash flow report is available in Filament.
- Filament cash-flow report access and totals have focused feature tests.
- Contribution report exists.
- Contribution report totals are extracted to `BuildContributionReport`.
- Contribution report is available in Filament.
- Contribution report totals and Filament access have focused feature tests.

## Not Done

### Stack Direction

- Laravel 13 is no longer a mandatory upgrade target; keep the current Laravel version unless a clear maintenance or compatibility reason appears.
- PHP 8.3+ is preferred, but the current PHP requirement can remain if there are no compatibility issues.
- Livewire 4 is optional and should only be introduced when a backend or member portal workflow clearly benefits from it.
- Filament 4 is installed and used for the admin panel.
- Filament Resources and pages are implemented for members, dependents, contribution records, the contribution grid, expenses, incomes, inventory, rentals, and certificate logs.
- Remaining back-office Filament work for dashboard and reports is the filterable dashboard page and reports landing page.
- Member self-service screens are currently Blade/controller-based; moving them to Livewire is optional, not required.

### Architecture Refactor

- The first Action extraction exists for member/dependent import.
- Rental workflow Actions exist for create, update, return, and delete.
- Contribution rules are extracted to `ContributionService`.
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
- Full policies are not implemented for members, finances, rentals, certificates, or reports.
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
- Rentals

Current gaps:

- Shared import/export architecture is not implemented.
- Current CSV member/dependent import is action-based.
- Current CSV member/dependent export is action-based.
- Expense import/export is implemented.
- Income import/export is implemented.
- Rental import/export is implemented.
- Member/dependent import returns an `ImportResult` summary.
- Expense import validation/reporting is implemented.
- Income import validation/reporting is implemented.
- Rental import validation/reporting is implemented.
- Stable export column definitions are documented in `docs/features/import-export.md`.
- Import file formats are documented in `docs/features/import-export.md`.

### Finance Improvements

- Income sources are duplicated in the old `IncomeController` and Filament `IncomeForm`.
- Expense categories are duplicated in the old `ExpenseController` and Filament `ExpenseForm`.
- Income and expense category/source enums or lookup tables are not implemented.
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

- Structured report exports are not implemented.
- Browser print/save-PDF support exists for some report views, but it is not a formal export workflow.
- Contribution report query logic is extracted to `BuildContributionReport`.
- Report filters are basic.
- Dashboard summary, cash-flow report, and contribution report totals have focused tests.
- No printable report layout is documented.

### Testing

- No feature tests exist for member CRUD.
- Authorization tests exist for the first role-protected route boundaries.
- Filament member resource access tests exist.
- Filament finance resource access tests exist.
- Filament dashboard summary tests exist.
- Filament cash-flow report tests exist.
- Member/dependent import tests exist.
- Member/dependent export tests exist.
- Contribution amount and accounting-period tests exist.
- Filament contribution grid tests exist.
- No tests exist for income and expense CRUD.
- Rental inventory synchronization tests exist.
- Rental import/export tests exist.
- No tests exist for certificate logs.
- Contribution report total tests exist.

### Documentation

- Feature-specific docs exist for authorization and import/export.
- Member portal flow is documented in `docs/features/member-portal.md`.
- Role and permission rules are documented.
- Import file formats are documented.
- Report definitions are not documented.

## Suggested Next Steps

1. Decide whether to replace staff-set temporary passwords with email invitations or account claiming tokens.
2. Continue migrating dashboard and reports into Filament.
3. Add more `docs/features/` documents as each feature area is refactored.
