<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Login Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'email', ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rule(Password::defaults())
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Leave blank when editing to keep the current password.'),
                        Select::make('role')
                            ->options(UserRole::options())
                            ->required()
                            ->default(UserRole::Staff->value)
                            ->native(false),
                    ]),
                Section::make('Member / Officer Link')
                    ->columns(1)
                    ->schema([
                        Select::make('member_id')
                            ->label('Linked member or officer')
                            ->relationship('member', 'name')
                            ->getSearchResultsUsing(fn (string $search): array => Member::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orderBy('name')
                                ->limit(20)
                                ->pluck('name', 'id')
                                ->all())
                            ->getOptionLabelUsing(fn ($value): ?string => Member::query()->find($value)?->name)
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Select the member record when this login belongs to a resident or officer. Officer positions are managed from the Officers screen.'),
                    ]),
            ]);
    }
}
