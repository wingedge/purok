<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\Income;

final class ExportIncomes
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'date',
            'source',
            'description',
            'amount',
            'rental_id',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        Income::query()
            ->orderBy('date')
            ->orderBy('id')
            ->chunk(500, function ($incomes) use ($stream): void {
                foreach ($incomes as $income) {
                    fputcsv($stream, $this->row($income));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(Income $income): array
    {
        return [
            $income->id,
            $income->date?->format('Y-m-d'),
            $income->source,
            $income->description,
            number_format((float) $income->amount, 2, '.', ''),
            $income->rental_id,
            $income->created_at?->format('Y-m-d H:i:s'),
            $income->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
