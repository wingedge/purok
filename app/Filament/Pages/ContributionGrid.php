<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Contributions\BuildContributionGrid;
use App\Actions\Contributions\DeleteContribution;
use App\Actions\Contributions\RecordContribution;
use App\Models\Member;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class ContributionGrid extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $navigationLabel = 'Contribution Grid';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'contribution-grid';

    protected string $view = 'filament.pages.contribution-grid';

    public string $viewType = 'month';

    public int $year;

    public int $month;

    public ?string $search = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage-contributions') ?? false;
    }

    public function mount(): void
    {
        $this->viewType = request()->query('view_type') === 'year' ? 'year' : 'month';
        $this->year = (int) request()->query('year', now()->year);
        $this->month = max(1, min(12, (int) request()->query('month', now()->month)));
        $this->search = filled(request()->query('search')) ? (string) request()->query('search') : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function grid(): array
    {
        return app(BuildContributionGrid::class)->execute(
            $this->viewType,
            $this->year,
            $this->month,
            $this->search,
        );
    }

    public function toggleContribution(
        int $memberId,
        string $weekStart,
        DeleteContribution $deleteContribution,
        RecordContribution $recordContribution,
    ): void
    {
        Gate::authorize('manage-contributions');

        $amountDeleted = $deleteContribution->execute($memberId, $weekStart);

        if ($amountDeleted !== null) {
            Notification::make()
                ->title('Contribution removed')
                ->success()
                ->send();

            return;
        }

        $member = Member::query()
            ->where('indigent', false)
            ->findOrFail($memberId);

        $recordContribution->execute($member, $weekStart);

        Notification::make()
            ->title('Contribution recorded')
            ->success()
            ->send();
    }
}
