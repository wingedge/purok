<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Exports\ExportIncomes;
use App\Actions\Imports\ImportIncomes;
use App\Actions\Incomes\CreateIncome;
use App\Actions\Incomes\DeleteIncome;
use App\Actions\Incomes\ListIncomes;
use App\Actions\Incomes\UpdateIncome;
use App\Models\Income;
use App\Support\Finance\IncomeSources;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomeController extends Controller
{
    public function index(ListIncomes $listIncomes)
    {
        $incomes = $listIncomes->execute();

        return view('incomes.index', compact('incomes'));
    }

    /**
     * @return array<int, string>
     */
    private function sources(): array
    {
        return IncomeSources::values();
    }

    public function create()
    {
        return view('incomes.create',[
            'sources' => $this->sources()
        ]);
    }

    public function store(Request $request, CreateIncome $createIncome)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $createIncome->execute($validated);

        return redirect()->route('incomes.index')
            ->with('success', 'Income added successfully.');
    }

    public function edit(Income $Income)
    {
        //return view('incomes.edit', compact('Income'));
        return view('incomes.update', [
            'income'    => $Income,
            'sources' => $this->sources(),
        ]);
    }

    public function update(Request $request, Income $Income, UpdateIncome $updateIncome)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $updateIncome->execute($Income, $validated);

        return redirect()->route('incomes.index')
            ->with('success', 'Income updated successfully.');
    }

    public function destroy(Income $Income, DeleteIncome $deleteIncome)
    {
        $deleteIncome->execute($Income);

        return redirect()->route('incomes.index')
            ->with('success', 'Income deleted successfully.');
    }

    public function export(ExportIncomes $exportIncomes): StreamedResponse
    {
        $filename = 'incomes-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(
            fn () => print $exportIncomes->execute(),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function import(Request $request, ImportIncomes $importIncomes)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        try {
            $result = $importIncomes->execute($request->file('csv_file')->getRealPath());

            return redirect()
                ->route('incomes.index')
                ->with('success', 'Incomes imported. '.$result->summary());
        } catch (\Exception $e) {
            return back()->withErrors([
                'csv_file' => 'Import failed: '.$e->getMessage(),
            ]);
        }
    }
}
