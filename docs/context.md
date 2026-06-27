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
- Role-based access exists for admin, treasurer, staff, and member users.
- Member-role users are blocked from the Filament admin panel.
- Users can be linked to member records through `users.member_id`.
- Member-facing profile and dependent self-service screens are implemented through Blade routes at `/member/profile`.
- Member users are redirected to the member portal after login and blocked from the back-office dashboard.
- Staff/admin can create or update a linked member portal account from the Filament member edit screen.
- Member users can view read-only contribution status for their own record in the portal.
