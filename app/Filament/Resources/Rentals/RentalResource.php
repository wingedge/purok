<?php

namespace App\Filament\Resources\Rentals;

use App\Filament\Resources\Rentals\Pages\CreateRental;
use App\Filament\Resources\Rentals\Pages\EditRental;
use App\Filament\Resources\Rentals\Pages\ListRentals;
use App\Filament\Resources\Rentals\Schemas\RentalForm;
use App\Filament\Resources\Rentals\Tables\RentalsTable;
use App\Models\Rental;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class RentalResource extends Resource
{
    protected static ?string $model = Rental::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Logistics';

    protected static ?string $navigationLabel = 'Rentals';

    protected static ?string $modelLabel = 'Rental';

    protected static ?string $pluralModelLabel = 'Rentals';

    public static function form(Schema $schema): Schema
    {
        return RentalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage-rentals') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage-rentals') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage-rentals') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('manage-rentals') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentals::route('/'),
            'create' => CreateRental::route('/create'),
            'edit' => EditRental::route('/{record}/edit'),
        ];
    }
}
