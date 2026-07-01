<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Exports\ExportContributions;
use App\Actions\Exports\ExportCommunityFundingDonations;
use App\Actions\Exports\ExportCommunityFundingEvents;
use App\Actions\Exports\ExportExpenses;
use App\Actions\Exports\ExportIncomes;
use App\Actions\Exports\ExportInventories;
use App\Actions\Exports\ExportMembers;
use App\Actions\Exports\ExportRentals;
use App\Actions\Imports\ImportContributions;
use App\Actions\Imports\ImportCommunityFundingDonations;
use App\Actions\Imports\ImportCommunityFundingEvents;
use App\Actions\Imports\ImportExpenses;
use App\Actions\Imports\ImportIncomes;
use App\Actions\Imports\ImportInventories;
use App\Actions\Imports\ImportMembers;
use App\Actions\Imports\ImportRentals;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use UnitEnum;

class DataExchange extends Page
{
    use WithFileUploads;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Import / Export';

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'data-exchange';

    protected string $view = 'filament.pages.data-exchange';

    public ?TemporaryUploadedFile $membersCsv = null;

    public ?TemporaryUploadedFile $expensesCsv = null;

    public ?TemporaryUploadedFile $incomesCsv = null;

    public ?TemporaryUploadedFile $contributionsCsv = null;

    public ?TemporaryUploadedFile $communityFundingEventsCsv = null;

    public ?TemporaryUploadedFile $communityFundingDonationsCsv = null;

    public ?TemporaryUploadedFile $inventoriesCsv = null;

    public ?TemporaryUploadedFile $rentalsCsv = null;

    public ?string $lastImportSummary = null;

    public static function canAccess(): bool
    {
        return static::canManageMembers()
            || static::canManageFinances()
            || static::canManageContributions()
            || static::canManageCommunityFunding()
            || static::canManageInventory()
            || static::canManageRentals();
    }

    public static function canManageMembers(): bool
    {
        return auth()->user()?->can('manage-members') ?? false;
    }

    public static function canManageFinances(): bool
    {
        return auth()->user()?->can('manage-finances') ?? false;
    }

    public static function canManageContributions(): bool
    {
        return auth()->user()?->can('manage-contributions') ?? false;
    }

    public static function canManageCommunityFunding(): bool
    {
        return auth()->user()?->can('manage-community-funding') ?? false;
    }

    public static function canManageInventory(): bool
    {
        return auth()->user()?->can('manage-inventory') ?? false;
    }

    public static function canManageRentals(): bool
    {
        return auth()->user()?->can('manage-rentals') ?? false;
    }

    public function importMembers(ImportMembers $importMembers): void
    {
        Gate::authorize('manage-members');

        $this->validateOnly('membersCsv', [
            'membersCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Members imported', fn (): string => $importMembers
            ->execute($this->membersCsv?->getRealPath() ?? '')
            ->summary());

        $this->membersCsv = null;
    }

    public function exportMembers(ExportMembers $exportMembers): StreamedResponse
    {
        Gate::authorize('manage-members');

        return $this->downloadCsv($exportMembers->execute(), 'members');
    }

    public function importExpenses(ImportExpenses $importExpenses): void
    {
        Gate::authorize('manage-finances');

        $this->validateOnly('expensesCsv', [
            'expensesCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Expenses imported', fn (): string => $importExpenses
            ->execute($this->expensesCsv?->getRealPath() ?? '', auth()->user())
            ->summary());

        $this->expensesCsv = null;
    }

    public function exportExpenses(ExportExpenses $exportExpenses): StreamedResponse
    {
        Gate::authorize('manage-finances');

        return $this->downloadCsv($exportExpenses->execute(), 'expenses');
    }

    public function importIncomes(ImportIncomes $importIncomes): void
    {
        Gate::authorize('manage-finances');

        $this->validateOnly('incomesCsv', [
            'incomesCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Incomes imported', fn (): string => $importIncomes
            ->execute($this->incomesCsv?->getRealPath() ?? '')
            ->summary());

        $this->incomesCsv = null;
    }

    public function exportIncomes(ExportIncomes $exportIncomes): StreamedResponse
    {
        Gate::authorize('manage-finances');

        return $this->downloadCsv($exportIncomes->execute(), 'incomes');
    }

    public function importContributions(ImportContributions $importContributions): void
    {
        Gate::authorize('manage-contributions');

        $this->validateOnly('contributionsCsv', [
            'contributionsCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Contributions imported', fn (): string => $importContributions
            ->execute($this->contributionsCsv?->getRealPath() ?? '')
            ->summary());

        $this->contributionsCsv = null;
    }

    public function exportContributions(ExportContributions $exportContributions): StreamedResponse
    {
        Gate::authorize('manage-contributions');

        return $this->downloadCsv($exportContributions->execute(), 'contributions');
    }

    public function importCommunityFundingEvents(ImportCommunityFundingEvents $importEvents): void
    {
        Gate::authorize('manage-community-funding');

        $this->validateOnly('communityFundingEventsCsv', [
            'communityFundingEventsCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Community funding events imported', fn (): string => $importEvents
            ->execute($this->communityFundingEventsCsv?->getRealPath() ?? '')
            ->summary());

        $this->communityFundingEventsCsv = null;
    }

    public function exportCommunityFundingEvents(ExportCommunityFundingEvents $exportEvents): StreamedResponse
    {
        Gate::authorize('manage-community-funding');

        return $this->downloadCsv($exportEvents->execute(), 'community-funding-events');
    }

    public function importCommunityFundingDonations(ImportCommunityFundingDonations $importDonations): void
    {
        Gate::authorize('manage-community-funding');

        $this->validateOnly('communityFundingDonationsCsv', [
            'communityFundingDonationsCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Community funding donations imported', fn (): string => $importDonations
            ->execute($this->communityFundingDonationsCsv?->getRealPath() ?? '')
            ->summary());

        $this->communityFundingDonationsCsv = null;
    }

    public function exportCommunityFundingDonations(ExportCommunityFundingDonations $exportDonations): StreamedResponse
    {
        Gate::authorize('manage-community-funding');

        return $this->downloadCsv($exportDonations->execute(), 'community-funding-donations');
    }

    public function importInventories(ImportInventories $importInventories): void
    {
        Gate::authorize('manage-inventory');

        $this->validateOnly('inventoriesCsv', [
            'inventoriesCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Inventories imported', fn (): string => $importInventories
            ->execute($this->inventoriesCsv?->getRealPath() ?? '')
            ->summary());

        $this->inventoriesCsv = null;
    }

    public function exportInventories(ExportInventories $exportInventories): StreamedResponse
    {
        Gate::authorize('manage-inventory');

        return $this->downloadCsv($exportInventories->execute(), 'inventories');
    }

    public function importRentals(ImportRentals $importRentals): void
    {
        Gate::authorize('manage-rentals');

        $this->validateOnly('rentalsCsv', [
            'rentalsCsv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $this->runImport('Rentals imported', fn (): string => $importRentals
            ->execute($this->rentalsCsv?->getRealPath() ?? '')
            ->summary());

        $this->rentalsCsv = null;
    }

    public function exportRentals(ExportRentals $exportRentals): StreamedResponse
    {
        Gate::authorize('manage-rentals');

        return $this->downloadCsv($exportRentals->execute(), 'rentals');
    }

    private function runImport(string $title, callable $callback): void
    {
        try {
            $summary = $callback();
            $this->lastImportSummary = $summary;

            Notification::make()
                ->title($title)
                ->body($summary)
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->addError('csv_file', 'Import failed: '.$e->getMessage());
        }
    }

    private function downloadCsv(string $csv, string $basename): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print $csv,
            $basename.'-'.now()->format('Y-m-d').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
