<?php

declare(strict_types=1);

namespace App\Actions\CommunityFunding;

use App\Models\CommunityFundingDonation;
use App\Models\CommunityFundingEvent;
use App\Models\Member;

final class RecordCommunityFundingDonation
{
    /**
     * @param array{amount:int|float|string, received_at:string, remarks?:string|null} $data
     */
    public function execute(CommunityFundingEvent $event, Member $member, array $data): CommunityFundingDonation
    {
        return CommunityFundingDonation::create([
            'community_funding_event_id' => $event->id,
            'member_id' => $member->id,
            'amount' => $data['amount'],
            'received_at' => $data['received_at'],
            'remarks' => $data['remarks'] ?? null,
        ]);
    }
}
