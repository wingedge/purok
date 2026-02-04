<div>
    <label class="block text-sm font-medium text-gray-700">
        Item Name
    </label>
    <input type="text"
           name="item_name"
           value="{{ old('item_name', $inventory->item_name ?? '') }}"
           class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">
        Total Quantity
    </label>
    <input type="number"
           name="total_quantity"
           value="{{ old('total_quantity', $inventory->total_quantity ?? '') }}"
           class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">
        Available Quantity
    </label>
    <input type="number"
           name="available_quantity"
           value="{{ old('available_quantity', $inventory->available_quantity ?? '') }}"
           class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">
        Rental Rate
    </label>
    <input type="number"
           name="rental_rate"
           value="{{ old('rental_rate', $inventory->rental_rate ?? '') }}"
           class="mt-1 block w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500">
</div>
