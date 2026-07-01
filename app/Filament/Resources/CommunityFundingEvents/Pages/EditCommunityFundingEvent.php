<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommunityFundingEvents\Pages;

use App\Actions\CommunityFunding\DeleteCommunityFundingEvent;
use App\Actions\CommunityFunding\UpdateCommunityFundingEvent;
use App\Filament\Resources\CommunityFundingEvents\CommunityFundingEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCommunityFundingEvent extends EditRecord
{
    protected static string $resource = CommunityFundingEventResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateCommunityFundingEvent::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(fn ($record) => app(DeleteCommunityFundingEvent::class)->execute($record)),
        ];
    }
}
