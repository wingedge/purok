<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;

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
            'Rentals - chairs / table rental',
            'Donation / Fund Drive',
            'Commission / Incentive',
            'Government Aid',
            'Penalties',
            'Misc',
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

}
