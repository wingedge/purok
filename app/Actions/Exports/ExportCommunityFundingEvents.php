<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\CommunityFundingEvent;

final class ExportCommunityFundingEvents
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'name',
            'description',
            'deadline',
            'goal_amount',
            'actual_amount',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        CommunityFundingEvent::query()
            ->withSum('donations', 'amount')
            ->orderBy('name')
            ->orderBy('id')
            ->chunk(500, function ($events) use ($stream): void {
                foreach ($events as $event) {
                    fputcsv($stream, $this->row($event));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(CommunityFundingEvent $event): array
    {
        return [
            $event->id,
            $event->name,
            $event->description,
            $event->deadline?->format('Y-m-d'),
            $event->goal_amount === null ? null : number_format((float) $event->goal_amount, 2, '.', ''),
            number_format($event->actual_amount, 2, '.', ''),
            $event->created_at?->format('Y-m-d H:i:s'),
            $event->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
