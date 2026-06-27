<?php

namespace App\Filament\Resources\Members\Pages;

use App\Actions\Members\CreateMemberPortalAccount;
use App\Filament\Resources\Members\MemberResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\Rules\Password;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('portalAccount')
                ->label(fn (): string => $this->record->user ? 'Update Portal Account' : 'Create Portal Account')
                ->icon('heroicon-o-user-plus')
                ->modalHeading(fn (): string => $this->record->user ? 'Update Portal Account' : 'Create Portal Account')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->default(fn (): string => $this->record->user?->name ?? $this->record->name),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->default(fn (): ?string => $this->record->user?->email ?? $this->record->email)
                        ->unique(
                            table: User::class,
                            column: 'email',
                            ignoreRecord: true,
                            ignorable: fn (): ?User => $this->record->user,
                        ),
                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->required(fn (): bool => $this->record->user === null)
                        ->rule(Password::defaults())
                        ->helperText(fn (): string => $this->record->user
                            ? 'Leave blank to keep the current password.'
                            : 'Share this temporary password with the member.'),
                ])
                ->action(function (array $data, CreateMemberPortalAccount $createMemberPortalAccount): void {
                    $createMemberPortalAccount->execute($this->record, $data);

                    Notification::make()
                        ->title('Portal account saved')
                        ->success()
                        ->send();

                    $this->record->refresh();
                }),
            DeleteAction::make(),
        ];
    }
}
