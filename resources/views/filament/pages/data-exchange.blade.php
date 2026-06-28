<x-filament-panels::page>
    @if ($lastImportSummary)
        <div class="purok-alert">
            {{ $lastImportSummary }}
        </div>
    @endif

    <div class="purok-exchange-grid">
        @can('manage-members')
            <section class="purok-exchange-card">
                <div>
                    <h2 class="purok-link-title">Members And Dependents</h2>
                    <p class="purok-link-description">Import or export member profile and dependent CSV data.</p>
                </div>

                <div class="purok-exchange-form">
                    <input type="file" wire:model="membersCsv" accept=".csv,text/csv,text/plain" class="purok-file-input">
                    @error('membersCsv')
                        <p class="purok-error">{{ $message }}</p>
                    @enderror

                    <div class="purok-fi-actions">
                        <button type="button" wire:click="importMembers" wire:loading.attr="disabled" class="fi-btn fi-color-primary fi-size-md">
                            Import CSV
                        </button>
                        <button type="button" wire:click="exportMembers" wire:loading.attr="disabled" class="fi-btn fi-color-gray fi-size-md">
                            Export CSV
                        </button>
                    </div>
                </div>
            </section>
        @endcan

        @can('manage-finances')
            <section class="purok-exchange-card">
                <div>
                    <h2 class="purok-link-title">Expenses</h2>
                    <p class="purok-link-description">Import or export dated expense CSV records.</p>
                </div>

                <div class="purok-exchange-form">
                    <input type="file" wire:model="expensesCsv" accept=".csv,text/csv,text/plain" class="purok-file-input">
                    @error('expensesCsv')
                        <p class="purok-error">{{ $message }}</p>
                    @enderror

                    <div class="purok-fi-actions">
                        <button type="button" wire:click="importExpenses" wire:loading.attr="disabled" class="fi-btn fi-color-primary fi-size-md">
                            Import CSV
                        </button>
                        <button type="button" wire:click="exportExpenses" wire:loading.attr="disabled" class="fi-btn fi-color-gray fi-size-md">
                            Export CSV
                        </button>
                    </div>
                </div>
            </section>

            <section class="purok-exchange-card">
                <div>
                    <h2 class="purok-link-title">Incomes</h2>
                    <p class="purok-link-description">Import or export income CSV records, including optional rental links.</p>
                </div>

                <div class="purok-exchange-form">
                    <input type="file" wire:model="incomesCsv" accept=".csv,text/csv,text/plain" class="purok-file-input">
                    @error('incomesCsv')
                        <p class="purok-error">{{ $message }}</p>
                    @enderror

                    <div class="purok-fi-actions">
                        <button type="button" wire:click="importIncomes" wire:loading.attr="disabled" class="fi-btn fi-color-primary fi-size-md">
                            Import CSV
                        </button>
                        <button type="button" wire:click="exportIncomes" wire:loading.attr="disabled" class="fi-btn fi-color-gray fi-size-md">
                            Export CSV
                        </button>
                    </div>
                </div>
            </section>
        @endcan

        @can('manage-rentals')
            <section class="purok-exchange-card">
                <div>
                    <h2 class="purok-link-title">Rentals</h2>
                    <p class="purok-link-description">Import or export rental CSV records and linked rental income.</p>
                </div>

                <div class="purok-exchange-form">
                    <input type="file" wire:model="rentalsCsv" accept=".csv,text/csv,text/plain" class="purok-file-input">
                    @error('rentalsCsv')
                        <p class="purok-error">{{ $message }}</p>
                    @enderror

                    <div class="purok-fi-actions">
                        <button type="button" wire:click="importRentals" wire:loading.attr="disabled" class="fi-btn fi-color-primary fi-size-md">
                            Import CSV
                        </button>
                        <button type="button" wire:click="exportRentals" wire:loading.attr="disabled" class="fi-btn fi-color-gray fi-size-md">
                            Export CSV
                        </button>
                    </div>
                </div>
            </section>
        @endcan
    </div>
</x-filament-panels::page>
