<?php

namespace App\Filament\Resources\Inventories;

use App\Filament\Resources\Inventories\Pages\CreateInventory;
use App\Filament\Resources\Inventories\Pages\EditInventory;
use App\Filament\Resources\Inventories\Pages\ListInventories;
use App\Filament\Resources\Inventories\Schemas\InventoryForm;
use App\Filament\Resources\Inventories\Tables\InventoriesTable;
use App\Models\Inventory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Logistics';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?string $modelLabel = 'Inventory Item';

    protected static ?string $pluralModelLabel = 'Inventory';

    public static function form(Schema $schema): Schema
    {
        return InventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoriesTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage-inventory') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage-inventory') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage-inventory') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('manage-inventory') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventories::route('/'),
            'create' => CreateInventory::route('/create'),
            'edit' => EditInventory::route('/{record}/edit'),
        ];
    }
}
