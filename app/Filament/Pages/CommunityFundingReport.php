<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Exports\ExportCommunityFundingReport;
use App\Actions\Reports\BuildCommunityFundingReport;
use App\Models\CommunityFundingEvent;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class CommunityFundingReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Community Funding';

    protected static ?int $navigationSort = 35;

    protected static ?string $slug = 'reports/community-funding';

    protected string $view = 'filament.pages.community-funding-report';

    public ?int $eventId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-cashflow-reports') ?? false;
    }

    public function mount(): void
    {
        $requestedEventId = request()->integer('event_id');
        $this->eventId = CommunityFundingEvent::query()
            ->whereKey($requestedEventId)
            ->value('id') ?? CommunityFundingEvent::query()->orderBy('name')->value('id');
    }

    /** @return Collection<int, CommunityFundingEvent> */
    public function events(): Collection
    {
        return CommunityFundingEvent::query()->orderBy('name')->get(['id', 'name']);
    }

    /** @return array<string, mixed>|null */
    public function report(): ?array
    {
        return $this->eventId === null ? null : app(BuildCommunityFundingReport::class)->execute($this->eventId);
    }

    public function exportExcel(ExportCommunityFundingReport $export): StreamedResponse
    {
        abort_if($this->eventId === null, 404, 'Select a community funding event first.');
        $event = CommunityFundingEvent::query()->findOrFail($this->eventId);
        $filename = str((string) $event->name)->slug()->limit(60, '')->value();

        return response()->streamDownload(
            fn () => print $export->execute($event->id),
            'community-funding-report-'.$filename.'.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        );
    }
}
