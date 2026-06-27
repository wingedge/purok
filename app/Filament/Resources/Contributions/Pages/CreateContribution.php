<?php

namespace App\Filament\Resources\Contributions\Pages;

use App\Actions\Contributions\RecordContribution;
use App\Filament\Resources\Contributions\ContributionResource;
use App\Models\Member;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateContribution extends CreateRecord
{
    protected static string $resource = ContributionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $member = Member::findOrFail($data['member_id']);

        return app(RecordContribution::class)->execute(
            $member,
            $data['week_start'],
            $data['remarks'] ?? null,
        );
    }
}
