<?php

declare(strict_types=1);

namespace App\Actions\CommunityFunding;

use App\Models\CommunityFundingEvent;

final class UpdateCommunityFundingEvent
{
    /**
     * @param array{name:string, description?:string|null, deadline?:string|null, goal_amount?:int|float|string|null} $data
     */
    public function execute(CommunityFundingEvent $event, array $data): CommunityFundingEvent
    {
        $event->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'goal_amount' => $data['goal_amount'] ?? null,
        ]);

        return $event->refresh();
    }
}
