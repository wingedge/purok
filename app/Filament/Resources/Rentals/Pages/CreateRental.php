<?php

namespace App\Filament\Resources\Rentals\Pages;

use App\Actions\Rentals\CreateRental as CreateRentalAction;
use App\Filament\Resources\Rentals\RentalResource;
use App\Models\Rental;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRental extends CreateRecord
{
    protected static string $resource = RentalResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateRentalAction::class)->execute($data);
    }
}
