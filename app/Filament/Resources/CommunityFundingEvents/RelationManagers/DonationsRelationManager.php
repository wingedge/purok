<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommunityFundingEvents\RelationManagers;

use App\Actions\CommunityFunding\DeleteCommunityFundingDonation;
use App\Actions\CommunityFunding\RecordCommunityFundingDonation;
use App\Actions\CommunityFunding\UpdateCommunityFundingDonation;
use App\Models\Member;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DonationsRelationManager extends RelationManager
{
    protected static string $relationship = 'donations';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('member_id')
                    ->label('Member')
                    ->options(fn (): array => Member::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->required(),
                TextInput::make('amount')
                    ->numeric()
                    ->minValue(0.01)
                    ->required(),
                DatePicker::make('received_at')
                    ->label('Date Received')
                    ->required(),
                Textarea::make('remarks')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('member.name')
            ->columns([
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('received_at')
                    ->label('Date Received')
                    ->date()
                    ->sortable(),
                TextColumn::make('remarks')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('received_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data) {
                        $member = Member::findOrFail($data['member_id']);

                        return app(RecordCommunityFundingDonation::class)->execute(
                            $this->getOwnerRecord(),
                            $member,
                            $data,
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function ($record, array $data) {
                        $member = Member::findOrFail($data['member_id']);

                        return app(UpdateCommunityFundingDonation::class)->execute(
                            $record,
                            $this->getOwnerRecord(),
                            $member,
                            $data,
                        );
                    }),
                DeleteAction::make()
                    ->using(fn ($record) => app(DeleteCommunityFundingDonation::class)->execute($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
