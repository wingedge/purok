<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\Contribution;

final class ExportContributions
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'member_id',
            'member_name',
            'week_start',
            'amount',
            'remarks',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        Contribution::query()
            ->with('member')
            ->orderBy('week_start')
            ->orderBy('member_id')
            ->chunk(500, function ($contributions) use ($stream): void {
                foreach ($contributions as $contribution) {
                    fputcsv($stream, $this->row($contribution));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(Contribution $contribution): array
    {
        return [
            $contribution->id,
            $contribution->member_id,
            $contribution->member?->name,
            $contribution->week_start?->format('Y-m-d'),
            number_format((float) $contribution->amount, 2, '.', ''),
            $contribution->remarks,
            $contribution->created_at?->format('Y-m-d H:i:s'),
            $contribution->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
