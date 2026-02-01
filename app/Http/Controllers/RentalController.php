<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with('inventory')
            ->latest()
            ->paginate(10);

        return view('rentals.index', compact('rentals'));
    }

    public function create()
    {
        $inventories = Inventory::orderBy('item_name')->get();
        return view('rentals.create', compact('inventories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_id'   => 'required|exists:inventories,id',
            'renter_name'    => 'required|string|max:255',
            'renter_contact' => 'required|string|max:255',
            'quantity'       => 'required|integer|min:1',
            'rent_date'      => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            $inventory = Inventory::lockForUpdate()->find($validated['inventory_id']);

            if ($inventory->available_quantity < $validated['quantity']) {
                abort(422, 'Not enough available inventory.');
            }

            Rental::create($validated);

            $inventory->decrement('available_quantity', $validated['quantity']);
        });

        return redirect()
            ->route('rentals.index')
            ->with('success', 'Rental created successfully.');
    }

    public function edit(Rental $rental)
    {
        $inventories = Inventory::orderBy('item_name')->get();
        return view('rentals.edit', compact('rental', 'inventories'));
    }

    public function update(Request $request, Rental $rental)
    {
        $validated = $request->validate([
            'renter_name'    => 'required|string|max:255',
            'renter_contact' => 'required|string|max:255',
            'return_date'    => 'nullable|date',
            'status'         => 'required|in:rented,returned',
        ]);

        DB::transaction(function () use ($validated, $rental) {
            if ($rental->status === 'rented' && $validated['status'] === 'returned') {
                $rental->inventory->increment('available_quantity', $rental->quantity);
                $validated['return_date'] = now();
            }

            $rental->update($validated);
        });

        return redirect()
            ->route('rentals.index')
            ->with('success', 'Rental updated successfully.');
    }

    public function destroy(Rental $rental)
    {
        DB::transaction(function () use ($rental) {
            if ($rental->status === 'rented') {
                $rental->inventory->increment('available_quantity', $rental->quantity);
            }

            $rental->delete();
        });

        return redirect()
            ->route('rentals.index')
            ->with('success', 'Rental deleted successfully.');
    }
}
