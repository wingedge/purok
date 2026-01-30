<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::withCount('dependents')->paginate(10);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'dependents.*.full_name' => 'nullable|string',
        ]);

        $member = Member::create($request->only([
            'full_name', 'address', 'phone', 'email'
        ]));

        if ($request->dependents) {
            foreach ($request->dependents as $dependent) {
                if (!empty($dependent['full_name'])) {
                    $member->dependents()->create($dependent);
                }
            }
        }

        return redirect()->route('members.index')
            ->with('success', 'Member added successfully');
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

    public function update(Request $request, Member $member)
    {
        $member->update($request->only([
            'full_name', 'address', 'phone', 'email'
        ]));

        // Reset dependents
        $member->dependents()->delete();

        if ($request->dependents) {
            foreach ($request->dependents as $dependent) {
                if (!empty($dependent['full_name'])) {
                    $member->dependents()->create($dependent);
                }
            }
        }

        return redirect()->route('members.index')
            ->with('success', 'Member updated');
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return back()->with('success', 'Member deleted');
    }
}
