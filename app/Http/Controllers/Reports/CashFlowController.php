<?php

namespace App\Http\Controllers\Reports;

use App\Actions\Reports\BuildCashFlowReport;
use App\Actions\Reports\BuildContributionReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CashFlowController extends Controller
{
    public function index(Request $request, BuildCashFlowReport $buildCashFlowReport)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month');
        $month = $month ? (int) $month : null;

        $report = $buildCashFlowReport->execute((int) $year, $month);

        return view('reports.cashflow', compact(
            'year',
            'month',
        ) + $report);
    }

    public function contributions(Request $request, BuildContributionReport $buildContributionReport)
    {
        $report = $buildContributionReport->execute(
            (int) $request->get('year', now()->year),
            $request->filled('start_month') ? (int) $request->get('start_month') : null,
            $request->filled('end_month') ? (int) $request->get('end_month') : null,
            $request->filled('month') ? (string) $request->get('month') : null,
        );

        return view('reports.contributions', $report);
    }
}
