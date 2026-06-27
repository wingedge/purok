<?php

namespace App\Filament\Resources\PurokCertificates;

use App\Filament\Resources\PurokCertificates\Pages\CreatePurokCertificate;
use App\Filament\Resources\PurokCertificates\Pages\EditPurokCertificate;
use App\Filament\Resources\PurokCertificates\Pages\ListPurokCertificates;
use App\Filament\Resources\PurokCertificates\Schemas\PurokCertificateForm;
use App\Filament\Resources\PurokCertificates\Tables\PurokCertificatesTable;
use App\Models\PurokCertificate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PurokCertificateResource extends Resource
{
    protected static ?string $model = PurokCertificate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $navigationLabel = 'Certificate Logs';

    protected static ?string $modelLabel = 'Certificate Log';

    protected static ?string $pluralModelLabel = 'Certificate Logs';

    public static function form(Schema $schema): Schema
    {
        return PurokCertificateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurokCertificatesTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage-certificates') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage-certificates') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage-certificates') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('manage-certificates') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurokCertificates::route('/'),
            'create' => CreatePurokCertificate::route('/create'),
            'edit' => EditPurokCertificate::route('/{record}/edit'),
        ];
    }
}
