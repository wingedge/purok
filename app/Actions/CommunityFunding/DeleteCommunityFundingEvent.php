<?php

declare(strict_types=1);

namespace App\Actions\CommunityFunding;

use App\Models\CommunityFundingEvent;

final class DeleteCommunityFundingEvent
{
    public function execute(CommunityFundingEvent $event): void
    {
        $event->delete();
    }
}
