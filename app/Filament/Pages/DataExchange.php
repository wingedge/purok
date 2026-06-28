<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Exports\ExportExpenses;
use App\Actions\Exports\ExportIncomes;
use App\Actions\Exports\ExportMembers;
use App\Actions\Exports\ExportRentals;
use App\Actions\Imports\ImportExpenses;
use App\Actions\Imports\ImportIncomes;
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

    public ?TemporaryUploadedFile $rentalsCsv = null;

    public ?string $lastImportSummary = null;

    public static function canAccess(): bool
    {
        return static::canManageMembers()
            || static::canManageFinances()
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
