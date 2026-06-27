<?php

namespace App\Filament\Resources\PurokCertificates\Pages;

use App\Filament\Resources\PurokCertificates\PurokCertificateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurokCertificate extends EditRecord
{
    protected static string $resource = PurokCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
