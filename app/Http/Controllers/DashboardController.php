<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\Income;
use App\Models\Member;
use App\Models\Rental;
use App\Models\Expense;
use App\Models\Contribution;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month'); // optional

        $dateFilter = function ($query) use ($year, $month) {
            $query->whereYear('created_at', $year);

            if ($month) {
                $query->whereMonth('created_at', $month);
            }
        };

        $totalMembers = Member::count();

        // Incomes
        $totalIncomes = Income::whereYear('date', $year)
            ->when($month, fn($q) => $q->whereMonth('date', $month))
            ->sum('amount');

        // Contributions
        $totalContributions = Contribution::where($dateFilter)->sum('amount');

        // Expenses
        $totalExpenses = Expense::whereYear('date', $year)
            ->when($month, fn($q) => $q->whereMonth('date', $month))
            ->sum('amount');

        // Members count
        $contributorsCount = Contribution::where($dateFilter)
            ->distinct('member_id')
            ->count('member_id');

        // Rentals
        $totalRentals = Rental::whereYear('created_at', $year)
            ->when($month, fn($q) => $q->whereMonth('created_at', $month))
            ->count();

        $totalFunds = ($totalIncomes + $totalContributions) - $totalExpenses;

        return view('dashboard', compact(
            'year',
            'month',
            'totalIncomes',
            'totalContributions',
            'totalExpenses',
            'contributorsCount',
            'totalRentals',
            'totalFunds',
            'totalMembers'
        ));
    }
}
