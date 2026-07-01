<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommunityFundingEvents;

use App\Filament\Resources\CommunityFundingEvents\Pages\CreateCommunityFundingEvent;
use App\Filament\Resources\CommunityFundingEvents\Pages\EditCommunityFundingEvent;
use App\Filament\Resources\CommunityFundingEvents\Pages\ListCommunityFundingEvents;
use App\Filament\Resources\CommunityFundingEvents\RelationManagers\DonationsRelationManager;
use App\Filament\Resources\CommunityFundingEvents\Schemas\CommunityFundingEventForm;
use App\Filament\Resources\CommunityFundingEvents\Tables\CommunityFundingEventsTable;
use App\Models\CommunityFundingEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CommunityFundingEventResource extends Resource
{
    protected static ?string $model = CommunityFundingEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $navigationLabel = 'Community Funding';

    protected static ?string $modelLabel = 'Community Funding Event';

    protected static ?string $pluralModelLabel = 'Community Funding';

    public static function form(Schema $schema): Schema
    {
        return CommunityFundingEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunityFundingEventsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withSum('donations', 'amount');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view-community-funding') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('manage-community-funding') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('manage-community-funding') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('manage-community-funding') ?? false;
    }

    public static function getRelations(): array
    {
        return [
            DonationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunityFundingEvents::route('/'),
            'create' => CreateCommunityFundingEvent::route('/create'),
            'edit' => EditCommunityFundingEvent::route('/{record}/edit'),
        ];
    }
}
