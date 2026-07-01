<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Models\CommunityFundingDonation;

final class ExportCommunityFundingDonations
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'id',
            'community_funding_event_id',
            'community_funding_event_name',
            'member_id',
            'member_name',
            'amount',
            'received_at',
            'remarks',
            'created_at',
            'updated_at',
        ];
    }

    public function execute(): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $this->headers());

        CommunityFundingDonation::query()
            ->with(['event', 'member'])
            ->orderBy('received_at')
            ->orderBy('id')
            ->chunk(500, function ($donations) use ($stream): void {
                foreach ($donations as $donation) {
                    fputcsv($stream, $this->row($donation));
                }
            });

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    /**
     * @return array<int, string|int|null>
     */
    private function row(CommunityFundingDonation $donation): array
    {
        return [
            $donation->id,
            $donation->community_funding_event_id,
            $donation->event?->name,
            $donation->member_id,
            $donation->member?->name,
            number_format((float) $donation->amount, 2, '.', ''),
            $donation->received_at?->format('Y-m-d'),
            $donation->remarks,
            $donation->created_at?->format('Y-m-d H:i:s'),
            $donation->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
