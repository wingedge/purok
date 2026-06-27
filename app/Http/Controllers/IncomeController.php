<?php

namespace App\Http\Controllers;

use App\Actions\Exports\ExportIncomes;
use App\Actions\Imports\ImportIncomes;
use App\Models\Income;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomeController extends Controller
{
    public function index()
    {
        $incomes = Income::orderBy('date', 'desc')
            ->paginate(10);

        return view('incomes.index', compact('incomes'));
    }

    private function sources(): array
    {
        return [
            'Rentals - Chairs / Table rental',
            'Donation / Fund Drive',
            'Commission / Incentive',
            'Government Aid',
            'Penalties',
            'Misc',
            'Cash on Hand',
        ];
    }

    public function create()
    {
        return view('incomes.create',[
            'sources' => $this->sources()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        Income::create([
            'date' => $request->date,
            'source' => $request->source,
            'description' => $request->description,
            'amount' => $request->amount,
        ]);

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

    public function update(Request $request, Income $Income)
    {
        $request->validate([
            'date' => 'required|date',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $Income->update($request->only('date', 'source', 'description', 'amount'));

        return redirect()->route('incomes.index')
            ->with('success', 'Income updated successfully.');
    }

    public function destroy(Income $Income)
    {
        $Income->delete();

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
