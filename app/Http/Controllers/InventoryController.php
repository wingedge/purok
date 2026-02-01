<?php
namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::latest()->paginate(10);
        return view('inventories.index', compact('inventories'));
    }

    public function create()
    {
        return view('inventories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'total_quantity' => 'required|integer|min:0',
            'available_quantity' => 'required|integer|min:0',
        ]);

        Inventory::create($validated);

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Inventory item created successfully.');
    }

    public function show(Inventory $inventory)
    {
        return view('inventories.show', compact('inventory'));
    }

    public function edit(Inventory $inventory)
    {
        return view('inventories.edit', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'total_quantity' => 'required|integer|min:0',
            'available_quantity' => 'required|integer|min:0',
        ]);

        $inventory->update($validated);

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Inventory item deleted successfully.');
    }
}
