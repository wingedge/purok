<?php

namespace App\Filament\Resources\Rentals\Pages;

use App\Actions\Rentals\DeleteRental;
use App\Actions\Rentals\UpdateRental;
use App\Filament\Resources\Rentals\RentalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditRental extends EditRecord
{
    protected static string $resource = RentalResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['amount'] = $this->record->income?->amount;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['inventory_id'] = $record->inventory_id;

        return app(UpdateRental::class)->execute($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (): mixed => app(DeleteRental::class)->execute($this->record)),
        ];
    }
}
