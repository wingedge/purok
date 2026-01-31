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
        $members = Member::with(['contributions' => function ($q) use ($weeks) {
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

    public function store(Request $request)
    {
        $request->validate([
            'member_id'  => 'required|exists:members,id',
            'week_start' => 'required|date',
            'amount'     => 'required|numeric|min:0',
        ]);

        Contribution::updateOrCreate(
            [
                'member_id'  => $request->member_id,
                'week_start' => $request->week_start,
            ],
            [
                'amount'     => $request->amount,                
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Saved']);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'member_id'  => 'required|exists:members,id',
            'week_start' => 'required|date',
        ]);

        Contribution::where('member_id', $request->member_id)
            ->where('week_start', $request->week_start)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Removed']);
    }
    
}

