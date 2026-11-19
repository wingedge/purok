<?php

declare(strict_types=1);

namespace App\Filament\Resources\Contributions\Pages;

use App\Actions\Contributions\RecordContributionPayment;
use App\Filament\Resources\Contributions\ContributionResource;
use App\Models\Member;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateContribution extends CreateRecord
{
    protected static string $resource = ContributionResource::class;

    public function getHeading(): string
    {
        return 'Record Member Payment';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $member = Member::findOrFail($data['member_id']);

        $contributions = app(RecordContributionPayment::class)->execute(
            $member,
            (int) $data['contribution_year'],
            (float) $data['payment_amount'],
            $data['remarks'] ?? null,
        );

        return $contributions->firstOrFail();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Member payment recorded';
    }

    protected function getRedirectUrl(): string
    {
        return ContributionResource::getUrl('index');
    }
}
