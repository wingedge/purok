<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Exports\ExportMembers;
use App\Actions\Imports\ImportMembers;
use App\Actions\Members\CreateMember;
use App\Actions\Members\DeleteMember;
use App\Actions\Members\ListMembers;
use App\Actions\Members\UpdateMember;
use App\Models\Member;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberController extends Controller
{
    public function index(Request $request, ListMembers $listMembers)
    {
        $search = $request->input('search');

        $members = $listMembers->execute(is_string($search) ? $search : null);

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request, CreateMember $createMember)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',
            'indigent'  => 'nullable|boolean',
            'birthday'  => 'nullable|date',
            'dependents.*.name' => 'nullable|string|max:255',
            'dependents.*.relationship' => 'nullable|string|max:255',
        ]);

        $createMember->execute([
            ...$validated,
            'indigent' => $request->boolean('indigent'),
            'dependents' => $request->input('dependents', []),
        ]);

        return redirect()
            ->route('members.index')
            ->with('success', 'Member created successfully.');
    }

    public function show(Member $member)
    {
        $member->load('dependents');
        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        $member->load('dependents');
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member, UpdateMember $updateMember)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'email'     => 'nullable|email|max:255',
            'indigent'  => 'nullable|boolean',
            'birthday'  => 'nullable|date',
            'dependents.*.name' => 'nullable|string|max:255',
            'dependents.*.relationship' => 'nullable|string|max:255',
        ]);

        $updateMember->execute($member, [
            ...$validated,
            'indigent' => $request->boolean('indigent'),
            'dependents' => $request->input('dependents', []),
        ]);

        return redirect()
            ->route('members.index')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member, DeleteMember $deleteMember)
    {
        $deleteMember->execute($member);

        return redirect()
            ->route('members.index')
            ->with('success', 'Member deleted.');
    }

    public function export(ExportMembers $exportMembers): StreamedResponse
    {
        $filename = 'members-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(
            fn () => print $exportMembers->execute(),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * CSV IMPORT
     */
    public function import(Request $request, ImportMembers $importMembers)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $result = $importMembers->execute($request->file('csv_file')->getRealPath());

            return redirect()
                ->route('members.index')
                ->with('success', 'Members imported. '.$result->summary());

        } catch (\Exception $e) {
            return back()->withErrors([
                'csv_file' => 'Import failed: ' . $e->getMessage()
            ]);
        }
    }

}
