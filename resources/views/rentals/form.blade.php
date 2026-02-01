<div>
    <label class="block text-sm font-medium text-gray-700">Item</label>
    <select name="inventory_id"
            class="mt-1 block w-full rounded border-gray-300"
            {{ isset($edit) ? 'disabled' : '' }}>
        @foreach($inventories as $inventory)
            <option value="{{ $inventory->id }}"
                @selected(old('inventory_id', $rental->inventory_id ?? '') == $inventory->id)>
                {{ $inventory->item_name }} (Available: {{ $inventory->available_quantity }})
            </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Renter Name</label>
    <input type="text" name="renter_name"
           value="{{ old('renter_name', $rental->renter_name ?? '') }}"
           class="mt-1 block w-full rounded border-gray-300">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Renter Contact</label>
    <input type="text" name="renter_contact"
           value="{{ old('renter_contact', $rental->renter_contact ?? '') }}"
           class="mt-1 block w-full rounded border-gray-300">
</div>

@if(!isset($edit))
<div>
    <label class="block text-sm font-medium text-gray-700">Quantity</label>
    <input type="number" name="quantity"
           value="{{ old('quantity') }}"
           class="mt-1 block w-full rounded border-gray-300">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Rent Date</label>
    <input type="date" name="rent_date"
           value="{{ old('rent_date', now()->toDateString()) }}"
           class="mt-1 block w-full rounded border-gray-300">
</div>
@endif

@if(isset($edit))
<div>
    <label class="block text-sm font-medium text-gray-700">Status</label>
    <select name="status" class="mt-1 block w-full rounded border-gray-300">
        <option value="rented" @selected($rental->status === 'rented')>Rented</option>
        <option value="returned" @selected($rental->status === 'returned')>Returned</option>
    </select>
</div>
@endif
