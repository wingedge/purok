<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\Expense;

final class ExportExpenses
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'date',
            'category',
            'description',
            'amount',
            'created_by',
            'created_by_name',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        Expense::query()
            ->with('creator')
            ->orderBy('date')
            ->orderBy('id')
            ->chunk(500, function ($expenses) use ($stream): void {
                foreach ($expenses as $expense) {
                    fputcsv($stream, $this->row($expense));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(Expense $expense): array
    {
        return [
            $expense->id,
            $expense->date?->format('Y-m-d'),
            $expense->category,
            $expense->description,
            number_format((float) $expense->amount, 2, '.', ''),
            $expense->created_by,
            $expense->creator?->name,
            $expense->created_at?->format('Y-m-d H:i:s'),
            $expense->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
