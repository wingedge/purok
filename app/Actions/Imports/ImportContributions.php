<?php

declare(strict_types=1);

namespace App\Actions\Imports;

use App\Actions\Contributions\RecordContribution;
use App\Data\Imports\FailedImportRow;
use App\Data\Imports\ImportResult;
use App\Models\Contribution;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class ImportContributions
{
    public function __construct(
        private readonly RecordContribution $recordContribution,
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
                    'member_id' => ['nullable', 'integer', 'exists:members,id'],
                    'member_name' => ['nullable', 'string', 'max:255'],
                    'week_start' => ['required', 'date'],
                    'remarks' => ['nullable', 'string'],
                ]);

                $validator->after(function ($validator) use ($data): void {
                    if (empty($data['member_id']) && empty($data['member_name'])) {
                        $validator->errors()->add('member', 'Either member_id or member_name is required.');
                    }
                });

                if ($validator->fails()) {
                    $result = $result->withFailedRow(new FailedImportRow(
                        rowNumber: $rowNumber,
                        data: $data,
                        errors: $validator->errors()->toArray(),
                    ));
                    continue;
                }

                $member = $this->member($data);

                if ($member === null) {
                    $result = $result->withFailedRow(new FailedImportRow(
                        rowNumber: $rowNumber,
                        data: $data,
                        errors: ['member' => ['Member could not be found or matched uniquely.']],
                    ));
                    continue;
                }

                $weekStart = Carbon::parse((string) $data['week_start'])->toDateString();
                $exists = Contribution::query()
                    ->where('member_id', $member->id)
                    ->whereDate('week_start', $weekStart)
                    ->exists();

                $this->recordContribution->execute($member, $weekStart, $data['remarks'] ?? null);

                $result = $exists ? $result->withUpdated() : $result->withCreated();
            }

            return $result;
        });
    }

    /**
     * @param array<string, string|null> $data
     */
    private function member(array $data): ?Member
    {
        if (! empty($data['member_id'])) {
            return Member::query()->find((int) $data['member_id']);
        }

        $matches = Member::query()
            ->where('name', $data['member_name'])
            ->limit(2)
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
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
