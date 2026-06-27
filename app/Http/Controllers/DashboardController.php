<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\BuildDashboardSummary;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, BuildDashboardSummary $buildDashboardSummary)
    {
        $year  = $request->get('year', now()->year);
        $month = $request->get('month'); // optional
        $month = $month ? (int) $month : null;

        $summary = $buildDashboardSummary->execute((int) $year, $month);

        return view('dashboard', compact(
            'year',
            'month'
        ) + $summary);
    }
}
