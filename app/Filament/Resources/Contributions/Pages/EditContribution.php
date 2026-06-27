<?php

namespace App\Filament\Resources\Contributions\Pages;

use App\Actions\Contributions\RecordContribution;
use App\Filament\Resources\Contributions\ContributionResource;
use App\Models\Member;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditContribution extends EditRecord
{
    protected static string $resource = ContributionResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $member = Member::findOrFail($data['member_id']);
        $updated = app(RecordContribution::class)->execute(
            $member,
            $data['week_start'],
            $data['remarks'] ?? null,
        );

        if (! $updated->is($record)) {
            $record->delete();
        }

        return $updated;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
