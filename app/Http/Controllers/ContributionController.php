<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Contribution;
use Illuminate\Http\Request;

class ContributionController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $currentYear = \Carbon\Carbon::parse($month)->year;

        $start = \Carbon\Carbon::parse($month)->startOfMonth();
        $end   = \Carbon\Carbon::parse($month)->endOfMonth();

        // Generate Sunday inside the month
        $cursor = $start->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);

        // Ensure first Sunday is inside the month
        if ($cursor->month !== $start->month) {
            $cursor->addWeek();
        }

        $weeks = [];
        while ($cursor->lte($end)) {
            $weeks[] = $cursor->copy();
            $cursor->addWeek();
        }

        // 2. Fetch Members with two types of contribution data
        $members = Member::query()
        ->where('indigent', false) // <--- Add this line to filter out indigent members
        ->with(['contributions' => function ($q) use ($weeks) {
            // Only load contributions for the visible grid weeks (saves memory)
            $q->whereIn('week_start', collect($weeks)->map->toDateString());
        }])
        ->withSum(['contributions as year_total' => function ($q) use ($currentYear) {
            // Calculate the total for the entire selected year
            $q->whereYear('week_start', $currentYear);
        }], 'amount')
        ->orderBy('name')
        ->get();

        return view('contributions.index', compact('members', 'weeks', 'month', 'currentYear'));
    }

    // private function getContributionAmount()
    // {
    //     return 10.00; 
    // }

    private function getContributionAmount($memberId)
    {
        $member = Member::find($memberId);
        // If indigent is true, maybe they only pay 0 or 50?
        return $member->indigent ? 0.00 : 10.00;
    }

    public function store(Request $request)
    {
        // 1. Remove 'amount' from the validation
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'week_start' => 'required|date',
        ]);

        // 2. Define the amount here (Internal control)
        $amount = $this->getContributionAmount($request->member_id);

        // 3. Save to database
        $contribution = Contribution::updateOrCreate(
            [
                'member_id' => $request->member_id,
                'week_start' => $request->week_start,
            ],
            [
                'amount' => $amount,                
            ]
        );

        // 4. Return the amount so the JavaScript knows how much to add to the total
        return response()->json([
            'success' => true, 
            'amount' => $amount
        ]);
    }

    public function destroy(Request $request)
    {
        $contribution = Contribution::where('member_id', $request->member_id)
            ->where('week_start', $request->week_start)
            ->first();

        if ($contribution) {
            $amountDeleted = $contribution->amount;
            $contribution->delete();

            return response()->json([
                'success' => true,
                'amount' => $amountDeleted
            ]);
        }

        return response()->json(['success' => false], 404);
    }
    
}

