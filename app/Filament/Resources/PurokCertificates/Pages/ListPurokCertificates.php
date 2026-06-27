<?php

namespace App\Filament\Resources\PurokCertificates\Pages;

use App\Filament\Resources\PurokCertificates\PurokCertificateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPurokCertificates extends ListRecords
{
    protected static string $resource = PurokCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
