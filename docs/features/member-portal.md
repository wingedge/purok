# Member Portal

This document defines the target member-facing portal flow. The current app has back-office authentication, and users can now be linked to member records through `users.member_id`. The first member self-service profile screen is implemented with Blade and controller Actions.

## Goal

Members should be able to log in, view their own information, update their own profile, and manage their own dependents without accessing back-office records.

## Current State

- `users.member_id` links a user account to one member record.
- `users.role` supports a `member` role for member-facing accounts.
- Back-office roles still use the existing `role` column.
- Back-office users may have no linked member record.
- Member-role users cannot access the Filament admin panel.
- Member-facing routes exist at `/member/profile`.
- Linked member users can view their own member profile.
- Linked member users can update phone, email, birthday, and dependents.
- Member users are redirected to `/member/profile` after login.
- Member users are blocked from the back-office dashboard.
- Unlinked member users can view an account setup message but cannot update member data.
- Staff/admin can create or update a linked portal account from the Filament member edit screen.
- Linked member users can view read-only contribution status for their own record.
- Member email updates sync to both the member record and linked user account.
- Member dependent changes are applied immediately and do not require staff approval.
- Members can change their portal password while logged in.
- Members can use the forgot-password email flow with their linked account email.

## Target Flow

1. Staff or admin creates or verifies a member record.
2. Staff or admin links a user account to that member.
3. The member logs in with their user account.
4. The member is routed to the member-facing profile screen.
5. The member can view and update only their own member profile.
6. The member can add, edit, or remove only their own dependents.
7. The member can view their own read-only contribution status.

## Access Rules

Allowed:

- A linked member user can view their own member profile.
- A linked member user can update their own phone, email, birthday, and other approved profile fields.
- A linked member user can manage dependents attached to their own member record.
- A linked member user can view only their own contribution status.
- A linked member user can filter full contribution history by year and optional month.
- A linked member user can change their own account password.

Not allowed:

- A member user cannot access back-office dashboards.
- A member user cannot view other members.
- A member user cannot manage contributions, finances, rentals, inventory, reports, or certificates.
- A member user cannot change their own indigent status unless approved later.

## Implementation Direction

- Add dedicated member portal routes separate from back-office routes.
- Use policies or gates that compare `auth()->user()->member_id` to the requested member record.
- Keep member portal components thin and delegate profile/dependent updates to Actions.
- Use Livewire for member-facing profile/dependent forms when Livewire is introduced.
- Keep Filament for back-office administration, not member self-service.

Current implementation:

- `App\Http\Controllers\MemberPortalController` handles member portal display and validation.
- `App\Actions\Members\UpdateMemberProfile` updates approved profile fields.
- `App\Actions\Members\SyncMemberDependents` replaces the member's dependent list from validated input.
- `App\Actions\Members\CreateMemberPortalAccount` creates or updates the linked member-role user account.
- `App\Actions\Members\BuildMemberContributionStatus` builds the member-only read-only contribution summary.
- `App\Filament\Resources\Members\Pages\EditMember` exposes the portal account action for staff/admin.
- `resources/views/member-portal/show.blade.php` renders the first Blade-based portal screen.

## Decisions

- Members can update email on both the member record and linked user account.
- Dependent changes do not require staff approval.
- Contribution status shows full filtered history, with year and optional month filters.
- Members can change passwords while logged in and use the existing forgot-password email flow.

## Open Decisions

- Whether to replace temporary-password setup with email invitations or account claiming tokens.
