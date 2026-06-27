<?php

namespace App\Http\Controllers;

use App\Actions\Exports\ExportRentals;
use App\Actions\Imports\ImportRentals;
use App\Actions\Rentals\CreateRental;
use App\Actions\Rentals\DeleteRental;
use App\Actions\Rentals\ReturnRental;
use App\Actions\Rentals\UpdateRental;
use App\Models\Rental;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function store(Request $request, CreateRental $createRental)
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
            $createRental->execute($validated);
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

    public function update(Request $request, Rental $rental, UpdateRental $updateRental)
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
            $updateRental->execute($rental, $validated);
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('rentals.index')->with('success', 'Rental and Income updated.');
    }
    
    public function destroy(Rental $rental, DeleteRental $deleteRental)
    {
        try {
            $deleteRental->execute($rental);

            return redirect()->route('rentals.index')
                ->with('success', 'Rental and associated income record deleted, and stock restored.');
                
        } catch (\Exception $e) {
            return back()->withErrors('Error during deletion: ' . $e->getMessage());
        }
    }

    public function returnItem(Rental $rental, ReturnRental $returnRental)
    {
        try {
            $returnRental->execute($rental);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Item marked as returned and inventory updated.');
    }

    public function export(ExportRentals $exportRentals): StreamedResponse
    {
        $filename = 'rentals-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(
            fn () => print $exportRentals->execute(),
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function import(Request $request, ImportRentals $importRentals)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        try {
            $result = $importRentals->execute($request->file('csv_file')->getRealPath());

            return redirect()
                ->route('rentals.index')
                ->with('success', 'Rentals imported. '.$result->summary());
        } catch (\Exception $e) {
            return back()->withErrors([
                'csv_file' => 'Import failed: '.$e->getMessage(),
            ]);
        }
    }
}
