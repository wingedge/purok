<?php

declare(strict_types=1);

namespace App\Actions\Imports;

use App\Actions\CommunityFunding\RecordCommunityFundingDonation;
use App\Actions\CommunityFunding\UpdateCommunityFundingDonation;
use App\Data\Imports\FailedImportRow;
use App\Data\Imports\ImportResult;
use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class ImportCommunityFundingDonations
{
    public function __construct(
        private readonly RecordCommunityFundingDonation $recordDonation,
        private readonly UpdateCommunityFundingDonation $updateDonation,
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
                    'id' => ['nullable', 'integer', 'exists:community_funding_donations,id'],
                    'community_funding_event_id' => ['nullable', 'integer', 'exists:community_funding_events,id'],
                    'community_funding_event_name' => ['nullable', 'string', 'max:255'],
                    'member_id' => ['nullable', 'integer', 'exists:members,id'],
                    'member_name' => ['nullable', 'string', 'max:255'],
                    'amount' => ['required', 'numeric', 'min:0.01'],
                    'received_at' => ['required', 'date'],
                    'remarks' => ['nullable', 'string'],
                ]);

                $validator->after(function ($validator) use ($data): void {
                    if (empty($data['community_funding_event_id']) && empty($data['community_funding_event_name'])) {
                        $validator->errors()->add('community_funding_event', 'Either community_funding_event_id or community_funding_event_name is required.');
                    }

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

                $event = $this->event($data);
                $member = $this->member($data);

                if ($event === null) {
                    $result = $result->withFailedRow(new FailedImportRow(
                        rowNumber: $rowNumber,
                        data: $data,
                        errors: ['community_funding_event' => ['Community funding event could not be found or matched uniquely.']],
                    ));
                    continue;
                }

                if ($member === null) {
                    $result = $result->withFailedRow(new FailedImportRow(
                        rowNumber: $rowNumber,
                        data: $data,
                        errors: ['member' => ['Member could not be found or matched uniquely.']],
                    ));
                    continue;
                }

                if (! empty($data['id'])) {
                    $donation = CommunityFundingDonation::query()->findOrFail((int) $data['id']);
                    $this->updateDonation->execute($donation, $event, $member, $data);
                    $result = $result->withUpdated();
                    continue;
                }

                $this->recordDonation->execute($event, $member, $data);
                $result = $result->withCreated();
            }

            return $result;
        });
    }

    /**
     * @param array<string, string|null> $data
     */
    private function event(array $data): ?CommunityFundingEvent
    {
        if (! empty($data['community_funding_event_id'])) {
            return CommunityFundingEvent::query()->find((int) $data['community_funding_event_id']);
        }

        $matches = CommunityFundingEvent::query()
            ->where('name', $data['community_funding_event_name'])
            ->limit(2)
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
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
