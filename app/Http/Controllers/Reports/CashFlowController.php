<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;
use App\Models\Income;
use App\Models\Member;
use App\Models\Expense;
use App\Models\Contribution;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month');

        // Inflows
        $incomeTotal = Income::whereYear('date', $year)
            ->when($month, fn ($q) => $q->whereMonth('date', $month))
            ->sum('amount');

        $contributionTotal = Contribution::whereYear('created_at', $year)
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->sum('amount');

        // Outflows
        $expenseTotal = Expense::whereYear('date', $year)
            ->when($month, fn ($q) => $q->whereMonth('date', $month))
            ->sum('amount');

        // Net Cash Flow
        $netCashFlow = ($incomeTotal + $contributionTotal) - $expenseTotal;

        return view('reports.cashflow', compact(
            'year',
            'month',
            'incomeTotal',
            'contributionTotal',
            'expenseTotal',
            'netCashFlow'
        ));
    }

    // Example Controller Logic
    public function contributions(Request $request)
    {
        // 1. Determine the Date Range
        $year = $request->get('year', now()->year);
        $startMonth = $request->get('start_month'); // e.g., "1"
        $endMonth = $request->get('end_month');     // e.g., "12"

        if ($startMonth && $endMonth) {
            // Specific Range or Whole Year
            $start = Carbon::create($year, $startMonth, 1)->startOfMonth();
            $end = Carbon::create($year, $endMonth, 1)->endOfMonth();
        } elseif ($request->has('month')) {
            // Single Specific Month (YYYY-MM)
            $start = Carbon::parse($request->month)->startOfMonth();
            $end = Carbon::parse($request->month)->endOfMonth();
        } else {
            // Default to Whole Current Year
            $start = Carbon::create($year, 1, 1)->startOfMonth();
            $end = Carbon::create($year, 12, 31)->endOfMonth();
        }

        // 2. Generate Weeks (Sundays) - Using your exact logic
        $weeks = [];
        $cursor = $start->copy()->startOfWeek(Carbon::SUNDAY);
        if ($cursor->lt($start)) { $cursor->addWeek(); }

        while ($cursor->lte($end)) {
            $weeks[] = $cursor->copy();
            $cursor->addWeek();
        }

        // 3. Fetch Data
        $members = Member::query()
            ->where('indigent', false)
            ->with(['contributions' => function ($q) use ($weeks) {
                $q->whereIn('week_start', collect($weeks)->map->toDateString());
            }])
            ->orderBy('name')
            ->get();

        return view('reports.contributions', compact('members', 'weeks', 'start', 'end', 'year'));
    }
}
