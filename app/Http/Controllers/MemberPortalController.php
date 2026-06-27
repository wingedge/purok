<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Members\BuildMemberContributionStatus;
use App\Actions\Members\SyncMemberDependents;
use App\Actions\Members\UpdateMemberProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function show(Request $request, BuildMemberContributionStatus $buildMemberContributionStatus): View
    {
        abort_unless($request->user()->isMember(), 403);

        $member = $request->user()->member?->load('dependents');
        $contributionStatus = null;

        if ($member !== null) {
            $validated = $request->validate([
                'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
                'month' => ['nullable', 'integer', 'between:1,12'],
            ]);

            $contributionStatus = $buildMemberContributionStatus->execute(
                $member,
                (int) ($validated['year'] ?? now()->year),
                (int) ($validated['month'] ?? now()->month),
            );
        }

        return view('member-portal.show', [
            'member' => $member,
            'contributionStatus' => $contributionStatus,
        ]);
    }

    public function update(
        Request $request,
        UpdateMemberProfile $updateMemberProfile,
        SyncMemberDependents $syncMemberDependents,
    ): RedirectResponse {
        abort_unless($request->user()->isMember(), 403);

        $member = $request->user()->member;

        abort_if($member === null, 403);

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'dependents' => ['nullable', 'array'],
            'dependents.*.name' => ['nullable', 'string', 'max:255'],
            'dependents.*.relationship' => ['nullable', 'string', 'max:255'],
        ]);

        $updateMemberProfile->execute($member, $validated);
        $syncMemberDependents->execute($member, $validated['dependents'] ?? []);

        return redirect()
            ->route('member.portal.show')
            ->with('success', 'Your profile has been updated.');
    }
}
