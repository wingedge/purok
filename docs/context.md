## Overview

Purok is a community website where purok data is gathered and processed. 

I houses purok members, purok income and expenses, logs purok certificates released, records rents and creates reports
for other members

---

# Goal

Goal is to revamp and improve the flow, use filament for all of the backend and allow members login and update their profile and dependents.

---

# Current Progress

- Filament 4 is installed and the admin panel is available at `/admin`.
- Back-office member and dependent management has started moving into Filament through `MemberResource` and `DependentsRelationManager`.
- Back-office officer management has moved into Filament through `OfficerResource`.
- Back-office finance CRUD has started moving into Filament through `ExpenseResource` and `IncomeResource`.
- Back-office logistics CRUD has started moving into Filament through `InventoryResource` and `RentalResource`.
- Back-office certificate log CRUD has moved into Filament through `PurokCertificateResource`.
- Back-office contribution record CRUD has moved into Filament through `ContributionResource`.
- Back-office monthly/yearly contribution grid has moved into Filament through `ContributionGrid`.
- Back-office dashboard summary stats are available in Filament through `DashboardStatsOverview`.
- Back-office filterable dashboard summary has moved into Filament through `DashboardSummary`.
- Back-office reports landing has moved into Filament through `Reports`.
- Back-office cash-flow reporting has started moving into Filament through `CashFlowReport`.
- Back-office contribution reporting has moved into Filament through `ContributionReport`.
- Back-office CSV import/export workflows are available in Filament through `DataExchange`.
- Back-office user and role management is available to admins in Filament through `UserResource`.
- Old Blade back-office entry pages redirect to their Filament equivalents.
- Old Blade/controller back-office mutation, import, export, and report-detail routes have been removed from public access; back-office work is routed through Filament.
- Role-based access exists for admin, treasurer, staff, and member users.
- Member-role users are blocked from the Filament admin panel.
- Users can be linked to member records through `users.member_id`.
- Member-facing profile and dependent self-service screens are implemented through Blade routes at `/member/profile`.
- Member users are redirected to the member portal after login and blocked from the back-office dashboard.
- Staff/admin can create or update a linked member portal account from the Filament member edit screen.
- Admins can create back-office users, assign roles, and link accounts to member/officer records from `/admin/users`.
- Member users can view read-only contribution status for their own record in the portal.
- Member portal email updates sync to the linked login account, and members can change or reset their account password.
- Expense CSV import/export is implemented through Actions and Filament `DataExchange`.
- Income CSV import/export is implemented through Actions and Filament `DataExchange`.
- Inventory CSV import/export is implemented through Actions and Filament `DataExchange`.
- Rental CSV import/export is implemented through Actions and Filament `DataExchange`.

## Filament Migration Status

Using Filament:

- Members and dependents
- Officers
- Expense CRUD
- Income CRUD
- Inventory CRUD
- Rental CRUD
- Purok certificate log CRUD
- Contribution record CRUD
- Contribution grid page
- Dashboard summary stats
- Filterable dashboard summary page
- Reports landing page
- Cash-flow report page
- Contribution report page
- CSV import/export page
- User and role management

Still using Blade/controller screens:

- Auth profile and member portal
