<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommunityFundingEvents\Pages;

use App\Actions\CommunityFunding\CreateCommunityFundingEvent as CreateCommunityFundingEventAction;
use App\Filament\Resources\CommunityFundingEvents\CommunityFundingEventResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCommunityFundingEvent extends CreateRecord
{
    protected static string $resource = CommunityFundingEventResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateCommunityFundingEventAction::class)->execute($data);
    }
}
