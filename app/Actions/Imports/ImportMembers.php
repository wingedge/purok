<?php

declare(strict_types=1);

namespace App\Actions\Imports;

use App\Data\Imports\FailedImportRow;
use App\Data\Imports\ImportResult;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class ImportMembers
{
    public function execute(string $path): ImportResult
    {
        $rows = array_map('str_getcsv', file($path) ?: []);

        if ($rows === []) {
            return (new ImportResult())->withSkipped();
        }

        $header = $this->normalizeHeader(array_shift($rows) ?? []);
        $result = new ImportResult();

        return DB::transaction(function () use ($rows, $header, $result): ImportResult {
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
                $validator = Validator::make($data, [
                    'name' => 'required|string|max:255',
                    'phone' => 'nullable|string',
                    'email' => 'nullable|email',
                    'birthday' => 'nullable|date',
                    'indigent' => 'boolean',
                    'dependent_names' => 'nullable|string',
                    'dependent_relationships' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    $result = $result->withFailedRow(new FailedImportRow(
                        rowNumber: $rowNumber,
                        data: $data,
                        errors: $validator->errors()->toArray(),
                    ));
                    continue;
                }

                $member = Member::create([
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'birthday' => $data['birthday'] ?? null,
                    'indigent' => $data['indigent'],
                ]);

                $this->createDependents(
                    member: $member,
                    names: $data['dependent_names'] ?? null,
                    relationships: $data['dependent_relationships'] ?? null,
                );

                $result = $result->withCreated();
            }

            return $result;
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
     * @return array<string, mixed>
     */
    private function normalizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            $value = trim((string) $value);
            $data[$key] = $value === '' ? null : $value;
        }

        $data['indigent'] = in_array(
            strtolower((string) ($data['indigent'] ?? '')),
            ['yes', '1', 'true'],
            true,
        );

        return $data;
    }

    private function createDependents(Member $member, ?string $names, ?string $relationships): void
    {
        if ($names === null || trim($names) === '') {
            return;
        }

        $relationshipValues = $relationships === null
            ? []
            : array_map('trim', explode('|', $relationships));

        foreach (explode('|', $names) as $index => $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $member->dependents()->create([
                'name' => $name,
                'relationship' => $relationshipValues[$index] ?? null,
            ]);
        }
    }
}
