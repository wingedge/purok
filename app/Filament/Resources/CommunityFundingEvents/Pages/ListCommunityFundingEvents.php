<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommunityFundingEvents\Pages;

use App\Filament\Resources\CommunityFundingEvents\CommunityFundingEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommunityFundingEvents extends ListRecords
{
    protected static string $resource = CommunityFundingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
