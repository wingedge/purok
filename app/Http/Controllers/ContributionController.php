<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Services\ContributionService;

class ContributionController extends Controller
{

    public function index(Request $request, ContributionService $contributions)
    {
        $viewType = $request->get('view_type', 'month');
        $search = $request->get('search');
        
        // Default to current year and current month
        $selectedYear = $request->get('year', now()->year);
        $selectedMonth = $request->get('month', now()->month);

        // 1. Determine Date Range
        if ($viewType === 'year') {
            $start = \Carbon\Carbon::create($selectedYear, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
        } else {
            $start = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        }

        $weeks = $contributions->sundaysBetween($start, $end)->all();

        // 3. Query Members
        $members = Member::query()
            ->where('indigent', false)
            ->when($search, function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->with(['contributions' => function ($q) use ($weeks) {
                $q->whereIn('week_start', collect($weeks)->map->toDateString());
            }])
            ->withSum(['contributions as year_total' => function ($q) use ($selectedYear) {
                $q->whereYear('week_start', $selectedYear);
            }], 'amount')
            ->orderBy('name')
            ->get();

        return view('contributions.index', compact(
            'members', 'weeks', 'selectedYear', 'selectedMonth', 'viewType', 'search'
        ));
    }

    // public function index(Request $request)
    // {
    //     $month = $request->get('month', now()->format('Y-m'));
    //     $currentYear = \Carbon\Carbon::parse($month)->year;

    //     $start = \Carbon\Carbon::parse($month)->startOfMonth();
    //     $end   = \Carbon\Carbon::parse($month)->endOfMonth();

    //     // Generate Sunday inside the month
    //     $cursor = $start->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);

    //     // Ensure first Sunday is inside the month
    //     if ($cursor->month !== $start->month) {
    //         $cursor->addWeek();
    //     }

    //     $weeks = [];
    //     while ($cursor->lte($end)) {
    //         $weeks[] = $cursor->copy();
    //         $cursor->addWeek();
    //     }

    //     // 2. Fetch Members with two types of contribution data
    //     $members = Member::query()
    //     ->where('indigent', false) // <--- Add this line to filter out indigent members
    //     ->with(['contributions' => function ($q) use ($weeks) {
    //         // Only load contributions for the visible grid weeks (saves memory)
    //         $q->whereIn('week_start', collect($weeks)->map->toDateString());
    //     }])
    //     ->withSum(['contributions as year_total' => function ($q) use ($currentYear) {
    //         // Calculate the total for the entire selected year
    //         $q->whereYear('week_start', $currentYear);
    //     }], 'amount')
    //     ->orderBy('name')
    //     ->get();

    //     return view('contributions.index', compact('members', 'weeks', 'month', 'currentYear'));
    // }

    // private function getContributionAmount()
    // {
    //     return 10.00; 
    // }

    public function store(Request $request, ContributionService $contributions)
    {
        // 1. Remove 'amount' from the validation
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'week_start' => 'required|date',
        ]);

        // 2. Define the amount here (Internal control)
        $member = Member::findOrFail($request->member_id);
        $amount = $contributions->amountFor($member);

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
