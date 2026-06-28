<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Expenses\CreateExpense;
use App\Actions\Expenses\DeleteExpense;
use App\Actions\Expenses\ListExpenses;
use App\Actions\Expenses\UpdateExpense;
use App\Actions\Exports\ExportExpenses;
use App\Actions\Imports\ImportExpenses;
use App\Models\Expense;
use App\Support\Finance\ExpenseCategories;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(ListExpenses $listExpenses)
    {
        $expenses = $listExpenses->execute();

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {        
        return view('expenses.create',[
            'categories' => $this->categories()
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function categories(): array
    {
        return ExpenseCategories::values();
    }

    public function store(Request $request, CreateExpense $createExpense)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $createExpense->execute($validated, $request->user());

        return redirect()->route('expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.update',[
            'expense' => $expense,
            'categories' => $this->categories()
        ]);
        //return view('expenses.update', compact('expense'));
    }

    public function update(Request $request, Expense $expense, UpdateExpense $updateExpense)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $updateExpense->execute($expense, $validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense, DeleteExpense $deleteExpense)
    {
        $deleteExpense->execute($expense);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function export(ExportExpenses $exportExpenses): StreamedResponse
    {
        $filename = 'expenses-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(
            fn () => print $exportExpenses->execute(),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function import(Request $request, ImportExpenses $importExpenses)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        try {
            $result = $importExpenses->execute(
                $request->file('csv_file')->getRealPath(),
                $request->user(),
            );

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Expenses imported. '.$result->summary());
        } catch (\Exception $e) {
            return back()->withErrors([
                'csv_file' => 'Import failed: '.$e->getMessage(),
            ]);
        }
    }
}
