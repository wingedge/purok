<?php

declare(strict_types=1);

namespace App\Actions\Imports;

use App\Actions\Rentals\CreateRental;
use App\Data\Imports\FailedImportRow;
use App\Data\Imports\ImportResult;
use App\Models\Income;
use App\Models\Inventory;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

final class ImportRentals
{
    public function __construct(
        private readonly CreateRental $createRental,
    ) {
    }

    public function execute(string $path): ImportResult
    {
        $rows = array_map('str_getcsv', file($path) ?: []);

        if ($rows === []) {
            return (new ImportResult())->withSkipped();
        }

        $header = $this->normalizeHeader(array_shift($rows) ?? []);
        $result = new ImportResult();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            if ($this->isBlankRow($row)) {
                $result = $result->withSkipped();
                continue;
            }

            $data = $this->combineRow($header, $row);

            if ($data === null) {
                $result = $result->withFailedRow(new FailedImportRow(
                    rowNumber: $rowNumber,
                    data: ['row' => $row],
                    errors: ['row' => ['Column count does not match the header.']],
                ));
                continue;
            }

            $data = $this->normalizeData($data);
            $data['status'] ??= 'rented';

            $validator = Validator::make($data, [
                'inventory_id' => ['required', 'integer', 'exists:inventories,id'],
                'renter_name' => ['required', 'string', 'max:255'],
                'renter_contact' => ['required', 'string', 'max:255'],
                'quantity' => ['required', 'integer', 'min:1'],
                'rent_date' => ['required', 'date'],
                'amount' => ['required', 'numeric', 'min:0'],
                'status' => ['nullable', Rule::in(['rented', 'returned'])],
                'return_date' => ['nullable', 'date', 'required_if:status,returned'],
            ]);

            if ($validator->fails()) {
                $result = $result->withFailedRow(new FailedImportRow(
                    rowNumber: $rowNumber,
                    data: $data,
                    errors: $validator->errors()->toArray(),
                ));
                continue;
            }

            try {
                $this->createFromRow($data);
                $result = $result->withCreated();
            } catch (Throwable $e) {
                $result = $result->withFailedRow(new FailedImportRow(
                    rowNumber: $rowNumber,
                    data: $data,
                    errors: ['row' => [$e->getMessage()]],
                ));
            }
        }

        return $result;
    }

    /**
     * @param array<string, string|null> $data
     */
    private function createFromRow(array $data): void
    {
        if (($data['status'] ?? 'rented') === 'rented') {
            $this->createRental->execute($data);
            return;
        }

        DB::transaction(function () use ($data): void {
            $inventory = Inventory::query()->findOrFail($data['inventory_id']);
            $quantity = (int) $data['quantity'];

            $rental = Rental::create([
                'inventory_id' => $inventory->id,
                'renter_name' => $data['renter_name'],
                'renter_contact' => $data['renter_contact'],
                'quantity' => $quantity,
                'rent_date' => $data['rent_date'],
                'return_date' => $data['return_date'],
                'status' => 'returned',
            ]);

            Income::create([
                'date' => $data['rent_date'],
                'source' => 'Rentals - Chairs / Table rental',
                'description' => "Historical rental for {$data['renter_name']} ({$inventory->item_name} x {$quantity})",
                'amount' => $data['amount'],
                'rental_id' => $rental->id,
            ]);
        });
    }

    /**
     * @param array<int, string|null> $header
     * @return array<int, string>
     */
    private function normalizeHeader(array $header): array
    {
        return array_map(
            fn (?string $column): string => trim((string) $column),
            $header,
        );
    }

    /**
     * @param array<int, string|null> $row
     */
    private function isBlankRow(array $row): bool
    {
        return count(array_filter($row, fn ($value): bool => trim((string) $value) !== '')) === 0;
    }

    /**
     * @param array<int, string> $header
     * @param array<int, string|null> $row
     * @return array<string, string|null>|null
     */
    private function combineRow(array $header, array $row): ?array
    {
        if (count($header) !== count($row)) {
            return null;
        }

        /** @var array<string, string|null>|false $combined */
        $combined = array_combine($header, $row);

        return $combined === false ? null : $combined;
    }

    /**
     * @param array<string, string|null> $data
     * @return array<string, string|null>
     */
    private function normalizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            $value = trim((string) $value);
            $data[$key] = $value === '' ? null : $value;
        }

        return $data;
    }
}
