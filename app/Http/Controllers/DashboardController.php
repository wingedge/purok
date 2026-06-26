<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Member;
use App\Models\Rental;
use App\Models\Expense;
use Illuminate\Http\Request;
use App\Services\ContributionService;

class DashboardController extends Controller
{
    public function index(Request $request, ContributionService $contributions)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month'); // optional
        $month = $month ? (int) $month : null;

        $totalMembers = Member::count();

        // Incomes
        $totalIncomes = Income::whereYear('date', $year)
            ->when($month, fn($q) => $q->whereMonth('date', $month))
            ->sum('amount');

        // Contributions
        $totalContributions = $contributions->totalForAccountingPeriod((int) $year, $month);

        // 1. Total contributions for the current calendar year (2026)
        $thisYearContributions = $contributions->totalForAccountingPeriod((int) $year);

        // 2. Total contributions for the last 7 days
        $recentContributions = $contributions->recentRecordedTotal();

        // Expenses
        $totalExpenses = Expense::whereYear('date', $year)
            ->when($month, fn($q) => $q->whereMonth('date', $month))
            ->sum('amount');

        // Members count
        $contributorsCount = $contributions->contributorCountForAccountingPeriod((int) $year, $month);

        // Rentals
        $totalRentals = Rental::whereYear('created_at', $year)
            ->when($month, fn($q) => $q->whereMonth('created_at', $month))
            ->count();

        $totalFunds = ($totalIncomes + $totalContributions) - $totalExpenses;

        return view('dashboard', compact(
            'thisYearContributions',
            'recentContributions',
            'totalIncomes',
            'totalContributions',
            'totalExpenses',
            'contributorsCount',
            'totalRentals',
            'totalFunds',
            'totalMembers',
            'year',
            'month'
        ));
    }
}
