# Member Portal

This document defines the target member-facing portal flow. The current app has back-office authentication, and users can now be linked to member records through `users.member_id`, but member self-service screens are not implemented yet.

## Goal

Members should be able to log in, view their own information, update their own profile, and manage their own dependents without accessing back-office records.

## Current State

- `users.member_id` links a user account to one member record.
- `users.role` supports a `member` role for member-facing accounts.
- Back-office roles still use the existing `role` column.
- Back-office users may have no linked member record.
- Member-role users cannot access the Filament admin panel.
- Member-facing routes, screens, and policies are not implemented yet.

## Target Flow

1. Staff or admin creates or verifies a member record.
2. Staff or admin links a user account to that member.
3. The member logs in with their user account.
4. The member is routed to a member-facing dashboard.
5. The member can view and update only their own member profile.
6. The member can add, edit, or remove only their own dependents.
7. Optional later: the member can view their own contribution status.

## Access Rules

Allowed:

- A linked member user can view their own member profile.
- A linked member user can update their own phone, email, birthday, and other approved profile fields.
- A linked member user can manage dependents attached to their own member record.

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

## Open Decisions

- Whether members can update email on the member record, user account, or both.
- Whether dependent changes require staff approval.
- Whether members can view contribution history or only current balance/status.
