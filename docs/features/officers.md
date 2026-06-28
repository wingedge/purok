# Officers

This document defines how purok officers are represented.

## Purpose

Officers are official community positions held by members, such as President, Secretary, Treasurer, Auditor, or PIO.

Officer records are separate from login roles. A member can be listed as a purok officer without automatically receiving access to the back-office system.

## Current State

- Officer records live in the `officers` table.
- Each officer belongs to a member through `member_id`.
- Officer management is available in Filament at `/admin/officers`.
- Admin users can link a login account to an officer by selecting the officer's member record in `/admin/users`.
- Staff and admin users can manage officer records through the existing `manage-members` permission.
- Treasurer and member-role users cannot access officer management by default.

## Fields

- `member_id`
- `position`
- `term_start`
- `term_end`
- `is_active`
- `notes`

## Rules

- Officer positions are stored as strings for now.
- Common position options are centralized in `App\Support\Community\OfficerPositions`.
- `term_end` must not be earlier than `term_start`.
- Deleting a member nulls the linked `member_id` on officer records, preserving officer history.

## Future Improvements

- Add public display of current officers if needed.
- Add printable officer roster.
- Add term overlap validation if only one active officer per position should be allowed.
- Move officer positions to enums or lookup tables if positions become configurable.
