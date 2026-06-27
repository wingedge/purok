<?php

namespace App\Filament\Resources\Incomes;

use App\Filament\Resources\Incomes\Pages\CreateIncome;
use App\Filament\Resources\Incomes\Pages\EditIncome;
use App\Filament\Resources\Incomes\Pages\ListIncomes;
use App\Filament\Resources\Incomes\Schemas\IncomeForm;
use App\Filament\Resources\Incomes\Tables\IncomesTable;
use App\Models\Income;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Finances';

    protected static ?string $navigationLabel = 'Incomes';

    protected static ?string $modelLabel = 'Income';

    protected static ?string $pluralModelLabel = 'Incomes';

    public static function form(Schema $schema): Schema
    {
        return IncomeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomesTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('manage-finances') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage-finances') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage-finances') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('manage-finances') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomes::route('/'),
            'create' => CreateIncome::route('/create'),
            'edit' => EditIncome::route('/{record}/edit'),
        ];
    }
}
