<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Contribution;

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
}
