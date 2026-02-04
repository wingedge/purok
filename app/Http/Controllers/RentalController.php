<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Rental;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    public function index()
    {
        // Adding 'inventory' and 'income' here makes the page load in 1 query instead of 50
        $rentals = Rental::with(['inventory', 'income'])
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
        $inventory = Inventory::findOrFail($request->inventory_id);

        $validated = $request->validate([
            'inventory_id'   => 'required|exists:inventories,id',
            'renter_name'    => 'required|string|max:255',
            'renter_contact' => 'required|string|max:255',
            'quantity'       => 'required|integer|min:1|max:' . $inventory->available_quantity,
            'rent_date'      => 'required|date',
            'amount'         => 'required|numeric|min:0', // New field
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $inventory = Inventory::lockForUpdate()->find($validated['inventory_id']);

                if ($inventory->available_quantity < $validated['quantity']) {
                    throw new \Exception('Not enough available inventory.');
                }

                // 1. Create the Rental
                $rental = Rental::create($validated);

                // 2. Create the Income Record
                Income::create([
                    'date'        => $validated['rent_date'],
                    'source'      => 'Rentals - Chairs / Table rental',
                    'description' => "Rental for {$validated['renter_name']} ({$inventory->item_name} x {$validated['quantity']})",
                    'amount'      => $validated['amount'],
                    'rental_id'   => $rental->id,
                ]);

                // 3. Update Inventory
                $inventory->decrement('available_quantity', $validated['quantity']);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rentals.index')->with('success', 'Rental and Payment recorded.');
    }

    public function edit(Rental $rental)
    {
        $inventories = Inventory::orderBy('item_name')->get();
        return view('rentals.edit', compact('rental', 'inventories'));
    }

    public function update(Request $request, Rental $rental)
    {
        $maxAvailable = $rental->inventory->available_quantity + $rental->quantity;

        $validated = $request->validate([
            'renter_name'    => 'required|string|max:255',
            'renter_contact' => 'required|string|max:255',
            'quantity'       => 'required|integer|min:1|max:' . $maxAvailable,
            'status'         => 'required|in:rented,returned',
            'amount'         => 'required|numeric|min:0',
            'rent_date'      => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($validated, $rental) {
                $inventory = $rental->inventory()->lockForUpdate()->first();

                // 1. Handle Inventory Stock Adjustments
                if ($rental->status === 'rented' && $validated['status'] === 'rented') {
                    $diff = $validated['quantity'] - $rental->quantity;
                    if ($inventory->available_quantity < $diff) {
                        throw new \Exception('Not enough stock to increase quantity.');
                    }
                    $inventory->decrement('available_quantity', $diff);
                }

                // 2. Handle Item Return
                if ($rental->status === 'rented' && $validated['status'] === 'returned') {
                    $inventory->increment('available_quantity', $rental->quantity);
                    $rental->return_date = now();
                }

                // 3. Sync the Income Record
                // We use updateOrCreate in case an old rental didn't have an income record yet
                Income::updateOrCreate(
                    ['rental_id' => $rental->id], 
                    [
                        'date'        => $validated['rent_date'],
                        'source'      => 'Rentals - Chairs / Table rental',
                        'description' => "Rental: {$validated['renter_name']} - {$inventory->item_name} x {$validated['quantity']}",
                        'amount'      => $validated['amount'],
                    ]
                );

                // 4. Update the Rental itself
                $rental->update($validated);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rentals.index')->with('success', 'Rental and Income updated.');
    }
    
    public function destroy(Rental $rental)
    {
        try {
            DB::transaction(function () use ($rental) {
                // 1. Restore Inventory Stock (only if it was still 'rented')
                if ($rental->status === 'rented') {
                    $rental->inventory()->increment('available_quantity', $rental->quantity);
                }

                // 2. Delete the linked Income entry
                // This works because of the hasOne relationship we defined
                if ($rental->income) {
                    $rental->income->delete();
                }

                // 3. Delete the Rental
                $rental->delete();
            });

            return redirect()->route('rentals.index')
                ->with('success', 'Rental and associated income record deleted, and stock restored.');
                
        } catch (\Exception $e) {
            return back()->withErrors('Error during deletion: ' . $e->getMessage());
        }
    }

    public function returnItem(Rental $rental)
    {
        // Prevent double-returning
        if ($rental->status === 'returned') {
            return back()->with('error', 'This item has already been returned.');
        }

        DB::transaction(function () use ($rental) {
            // 1. Restore Inventory
            $rental->inventory->increment('available_quantity', $rental->quantity);

            // 2. Update Rental Status
            $rental->update([
                'status' => 'returned',
                'return_date' => now()
            ]);
        });

        return back()->with('success', 'Item marked as returned and inventory updated.');
    }
}
