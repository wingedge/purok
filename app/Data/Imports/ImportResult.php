<?php

declare(strict_types=1);

namespace App\Data\Imports;

final readonly class ImportResult
{
    /**
     * @param array<int, FailedImportRow> $failedRows
     */
    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public int $skipped = 0,
        public array $failedRows = [],
    ) {
    }

    public function withCreated(int $count = 1): self
    {
        return new self(
            created: $this->created + $count,
            updated: $this->updated,
            skipped: $this->skipped,
            failedRows: $this->failedRows,
        );
    }

    public function withSkipped(int $count = 1): self
    {
        return new self(
            created: $this->created,
            updated: $this->updated,
            skipped: $this->skipped + $count,
            failedRows: $this->failedRows,
        );
    }

    public function withFailedRow(FailedImportRow $failedRow): self
    {
        return new self(
            created: $this->created,
            updated: $this->updated,
            skipped: $this->skipped,
            failedRows: [...$this->failedRows, $failedRow],
        );
    }

    public function failed(): int
    {
        return count($this->failedRows);
    }

    public function summary(): string
    {
        return "Created: {$this->created}. Updated: {$this->updated}. Skipped: {$this->skipped}. Failed: {$this->failed()}.";
    }
}
