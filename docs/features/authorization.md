# Authorization

This document defines the intended role and permission model for Purok. The application has a `role` column on users and now has initial gate-based route enforcement for the main back-office areas.

## Current State

- Users have one role: `admin`, `treasurer`, `staff`, or `member`.
- The `User` model has helper methods for these roles.
- All main back-office screens require authentication.
- Certificate routes require both authentication and email verification.
- Named gates are registered in `AuthServiceProvider`.
- Main business routes use `can:` middleware for the first permission boundaries.
- Model policies are implemented for members, incomes, expenses, inventory, rentals, and certificate logs.
- Full policies are not implemented yet for every remaining domain model.
- Users can be linked to member records through `users.member_id`.
- Filament admin panel access is restricted to admin, treasurer, and staff users.
- Admin-only user and role management is available in Filament at `/admin/users`.
- Member portal routes and screens are implemented for linked member profile, dependent, password, and contribution-status access.

## Target Roles

### Admin

Admin users can manage the whole back-office system.

Allowed:

- Manage users and roles.
- Link users to member/officer records through the member record.
- View dashboard and all reports.
- Manage members and dependents.
- Manage purok officers.
- Import and export members and dependents.
- Manage contributions.
- Manage incomes and expenses.
- Import and export incomes and expenses.
- Manage inventory and rentals.
- Import and export rentals.
- Manage purok certificate logs.
- Access Filament admin resources.

### Treasurer

Treasurer users handle financial records and financial reporting.

Allowed:

- View dashboard.
- View members and dependents.
- View contributions.
- Manage contributions.
- Manage incomes and expenses.
- Import and export incomes and expenses.
- View rentals and linked rental income.
- View inventory.
- View financial reports.
- Export financial reports when implemented.

Not allowed by default:

- Delete members.
- Manage users and roles.
- Delete inventory items.
- Delete rentals unless explicitly approved later.

### Staff

Staff users handle day-to-day community records and logistics.

Allowed:

- View dashboard.
- Manage members and dependents.
- Manage purok officers.
- Import and export members and dependents.
- Manage purok certificate logs.
- Manage inventory and rentals.
- Import and export rentals.
- View basic reports needed for operations.

Not allowed by default:

- Manage users and roles.
- Manage income and expense records directly.
- Export sensitive financial reports unless explicitly approved later.

### Member

Member access is a portal role. Member users are linked to one member record through `users.member_id` and use dedicated member portal routes.

Allowed target behavior:

- Log in to a member-facing portal.
- View and update their own profile.
- View and update their own dependents.
- View their own contribution status.

Not allowed:

- Access back-office screens.
- View other member records.
- Manage financial, inventory, rental, report, or certificate records.

## Permission Matrix

| Area | Admin | Treasurer | Staff | Member |
| --- | --- | --- | --- | --- |
| Dashboard | Yes | Yes | Yes | Portal-only summary later |
| Users and roles | Yes | No | No | No |
| Members/dependents view | Yes | Yes | Yes | Own record only |
| Members/dependents create/update | Yes | No | Yes | Own record only |
| Members/dependents delete | Yes | No | No | No |
| Members/dependents import/export | Yes | No | Yes | No |
| Officers | Yes | No | Yes | No |
| Contributions view | Yes | Yes | Yes | Own status later |
| Contributions manage | Yes | Yes | No | No |
| Incomes | Yes | Yes | No | No |
| Expenses | Yes | Yes | No | No |
| Income/expense import/export | Yes | Yes | No | No |
| Inventory view | Yes | Yes | Yes | No |
| Inventory manage | Yes | No | Yes | No |
| Rentals view | Yes | Yes | Yes | No |
| Rentals manage | Yes | No | Yes | No |
| Rental import/export | Yes | No | Yes | No |
| Certificate logs | Yes | No | Yes | No |
| Cash flow reports | Yes | Yes | No | No |
| Contribution reports | Yes | Yes | Yes | Own status later |

## Implementation Direction

- Continue expanding policies where model-level authorization is clearer than route-level gates.
- Add a small role middleware only if route grouping becomes clearer than named gates.
- Keep role checks out of Blade templates except for showing or hiding action controls.
- Enforce permissions in controllers, Livewire components, and Filament resources, not only in navigation.
- Use the `member_id` link on users when building member self-service.
- Add tests for every restricted route and destructive action.

## Open Decisions

- Whether staff can edit existing member records after creation.
- Whether treasurers can delete income/expense records or only create corrections.
- Whether staff can see contribution payment amounts or only paid/unpaid status.
- Whether member self-service should use the same `users.role` column or a separate guard/profile model.
