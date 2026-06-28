<?php

declare(strict_types=1);

namespace App\Filament\Resources\Officers;

use App\Filament\Resources\Officers\Pages\CreateOfficer;
use App\Filament\Resources\Officers\Pages\EditOfficer;
use App\Filament\Resources\Officers\Pages\ListOfficers;
use App\Filament\Resources\Officers\Schemas\OfficerForm;
use App\Filament\Resources\Officers\Tables\OfficersTable;
use App\Models\Officer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class OfficerResource extends Resource
{
    protected static ?string $model = Officer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $navigationLabel = 'Officers';

    protected static ?string $modelLabel = 'Officer';

    protected static ?string $pluralModelLabel = 'Officers';

    public static function form(Schema $schema): Schema
    {
        return OfficerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficersTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage-members') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage-members') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage-members') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('manage-members') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfficers::route('/'),
            'create' => CreateOfficer::route('/create'),
            'edit' => EditOfficer::route('/{record}/edit'),
        ];
    }
}
