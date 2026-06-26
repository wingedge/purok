<?php

declare(strict_types=1);

namespace App\Data\Imports;

final readonly class FailedImportRow
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, array<int, string>> $errors
     */
    public function __construct(
        public int $rowNumber,
        public array $data,
        public array $errors,
    ) {
    }
}
