<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'reports';

    protected string $view = 'filament.pages.reports';

    public static function canAccess(): bool
    {
        return (auth()->user()?->can('view-cashflow-reports') ?? false)
            || (auth()->user()?->can('view-contribution-reports') ?? false);
    }
}
