<div>
    <label class="block text-sm font-medium text-gray-700">Item</label>
        <select name="inventory_id" id="inventory_id"
            class="mt-1 block w-full rounded border-gray-300"
            {{ isset($edit) ? 'disabled' : '' }}>
        <option value="" disabled selected>-- Select Item --</option>
        @foreach($inventories as $inventory)
            <option value="{{ $inventory->id }}" 
                    data-stock="{{ $inventory->available_quantity }}"
                    data-rate="{{ $inventory->rental_rate }}"
                @selected(old('inventory_id', $rental->inventory_id ?? '') == $inventory->id)>
                {{ $inventory->item_name }} (₱{{ $inventory->rental_rate }}/unit) (Available: {{ $inventory->available_quantity }})
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
{{-- <div>
    <label class="block text-sm font-medium text-gray-700">Quantity</label>
    <input type="number" name="quantity"
           value="{{ old('quantity') }}"
           class="mt-1 block w-full rounded border-gray-300">
</div> --}}

<div>
    <label class="block text-sm font-medium text-gray-700" for="quantity">Quantity</label>
    <input 
        type="number" 
        name="quantity" 
        id="quantity" 
        min="1" 
        max="{{ $inventory->available_quantity }}" 
        value="{{ old('quantity', 1) }}"
        class="border-gray-300 rounded shadow-sm"
        {{ isset($rental) && $rental->status === 'returned' ? 'readonly' : 'required' }}
    >
    <p class="text-sm text-gray-500">Available: <span id="stock-display">0</span></p>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700">Rent Date</label>
    <input type="date" name="rent_date"
           value="{{ old('rent_date', now()->toDateString()) }}"
           class="mt-1 block w-full rounded border-gray-300">
</div>
@endif

<div class="mt-4">
    <label class="block font-medium text-sm text-gray-700">Payment Amount</label>
    <input 
        type="number" 
        step="0.01" 
        name="amount" 
        value="{{ old('amount', $rental->amount ?? '') }}" 
        class="border-gray-300 rounded shadow-sm" 
        {{ isset($rental) && $rental->status === 'returned' ? 'readonly' : 'required' }}
    >
</div>

@if(isset($edit))


<div>
    <label class="block text-sm font-medium text-gray-700">Status</label>
    <select name="status" class="mt-1 block w-full rounded border-gray-300">
        <option value="rented" @selected($rental->status === 'rented')>Rented</option>
        <option value="returned" @selected($rental->status === 'returned')>Returned</option>
    </select>
</div>
@endif


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const itemSelect = document.getElementById('inventory_id');
        const quantityInput = document.getElementById('quantity');
        const stockDisplay = document.getElementById('stock-display');

        function updateMaxQuantity() {
            // Get the selected option
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                // Get stock from the data-stock attribute we added
                const stock = selectedOption.getAttribute('data-stock');
                
                // Update input max and the helper text
                quantityInput.max = stock;
                stockDisplay.textContent = stock;

                // Optional: If current value is higher than new max, reset it
                if (parseInt(quantityInput.value) > parseInt(stock)) {
                    quantityInput.value = stock;
                }
            }
        }

        // Run on change
        itemSelect.addEventListener('change', updateMaxQuantity);

        // Run on page load (for validation errors/old input)
        updateMaxQuantity();

        function calculateTotal() {
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
                const qty = parseInt(quantityInput.value) || 0;
                
                // Auto-calculate the total
                amountInput.value = (rate * qty).toFixed(2);
            }
        }

        itemSelect.addEventListener('change', calculateTotal);
        quantityInput.addEventListener('input', calculateTotal);
    });
</script>