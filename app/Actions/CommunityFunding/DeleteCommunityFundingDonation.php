<?php

declare(strict_types=1);

namespace App\Actions\CommunityFunding;

use App\Models\CommunityFundingDonation;

final class DeleteCommunityFundingDonation
{
    public function execute(CommunityFundingDonation $donation): void
    {
        $donation->delete();
    }
}
