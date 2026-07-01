<?php

declare(strict_types=1);

namespace App\Actions\Imports;

use App\Actions\CommunityFunding\CreateCommunityFundingEvent;
use App\Actions\CommunityFunding\UpdateCommunityFundingEvent;
use App\Data\Imports\FailedImportRow;
use App\Data\Imports\ImportResult;
use App\Models\CommunityFundingEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class ImportCommunityFundingEvents
{
    public function __construct(
        private readonly CreateCommunityFundingEvent $createEvent,
        private readonly UpdateCommunityFundingEvent $updateEvent,
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
                    'id' => ['nullable', 'integer', 'exists:community_funding_events,id'],
                    'name' => ['required', 'string', 'max:255'],
                    'description' => ['nullable', 'string'],
                    'deadline' => ['nullable', 'date'],
                    'goal_amount' => ['nullable', 'numeric', 'min:0'],
                ]);

                if ($validator->fails()) {
                    $result = $result->withFailedRow(new FailedImportRow(
                        rowNumber: $rowNumber,
                        data: $data,
                        errors: $validator->errors()->toArray(),
                    ));
                    continue;
                }

                if (! empty($data['id'])) {
                    $event = CommunityFundingEvent::query()->findOrFail((int) $data['id']);
                    $this->updateEvent->execute($event, $data);
                    $result = $result->withUpdated();
                    continue;
                }

                $this->createEvent->execute($data);
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
