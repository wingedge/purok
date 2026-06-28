<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MemberPortalController;
use App\Filament\Pages\CashFlowReport;
use App\Filament\Pages\ContributionReport;
use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\Incomes\IncomeResource;
use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\PurokCertificates\PurokCertificateResource;
use App\Filament\Resources\Rentals\RentalResource;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/member/profile', [MemberPortalController::class, 'show'])
        ->name('member.portal.show');

    Route::patch('/member/profile', [MemberPortalController::class, 'update'])
        ->name('member.portal.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('members', fn () => redirect()->to(MemberResource::getUrl()))
        ->middleware('can:view-members')
        ->name('members.index');

    Route::get('members/create', fn () => redirect()->to(MemberResource::getUrl('create')))
        ->middleware('can:manage-members')
        ->name('members.create');

    Route::get('members/{member}', fn () => redirect()->to(MemberResource::getUrl()))
        ->middleware('can:view-members')
        ->name('members.show');

    Route::get('members/{member}/edit', fn (App\Models\Member $member) => redirect()->to(MemberResource::getUrl('edit', ['record' => $member])))
        ->middleware('can:manage-members')
        ->name('members.edit');

    Route::get('expenses', fn () => redirect()->to(ExpenseResource::getUrl()))
        ->middleware('can:manage-finances')
        ->name('expenses.index');

    Route::get('expenses/create', fn () => redirect()->to(ExpenseResource::getUrl('create')))
        ->middleware('can:manage-finances')
        ->name('expenses.create');

    Route::get('expenses/{expense}/edit', fn (App\Models\Expense $expense) => redirect()->to(ExpenseResource::getUrl('edit', ['record' => $expense])))
        ->middleware('can:manage-finances')
        ->name('expenses.edit');

    Route::get('incomes', fn () => redirect()->to(IncomeResource::getUrl()))
        ->middleware('can:manage-finances')
        ->name('incomes.index');

    Route::get('incomes/create', fn () => redirect()->to(IncomeResource::getUrl('create')))
        ->middleware('can:manage-finances')
        ->name('incomes.create');

    Route::get('incomes/{income}/edit', fn (App\Models\Income $income) => redirect()->to(IncomeResource::getUrl('edit', ['record' => $income])))
        ->middleware('can:manage-finances')
        ->name('incomes.edit');

    Route::get('/contributions', fn () => redirect('/admin/contribution-grid'))->name('contributions.index');

    Route::get('inventories', fn () => redirect()->to(InventoryResource::getUrl()))
        ->middleware('can:manage-inventory')
        ->name('inventories.index');

    Route::get('inventories/create', fn () => redirect()->to(InventoryResource::getUrl('create')))
        ->middleware('can:manage-inventory')
        ->name('inventories.create');

    Route::get('inventories/{inventory}', fn () => redirect()->to(InventoryResource::getUrl()))
        ->middleware('can:manage-inventory')
        ->name('inventories.show');

    Route::get('inventories/{inventory}/edit', fn (App\Models\Inventory $inventory) => redirect()->to(InventoryResource::getUrl('edit', ['record' => $inventory])))
        ->middleware('can:manage-inventory')
        ->name('inventories.edit');

    Route::get('rentals', fn () => redirect()->to(RentalResource::getUrl()))
        ->middleware('can:manage-rentals')
        ->name('rentals.index');

    Route::get('rentals/create', fn () => redirect()->to(RentalResource::getUrl('create')))
        ->middleware('can:manage-rentals')
        ->name('rentals.create');

    Route::get('rentals/{rental}/edit', fn (App\Models\Rental $rental) => redirect()->to(RentalResource::getUrl('edit', ['record' => $rental])))
        ->middleware('can:manage-rentals')
        ->name('rentals.edit');
});

Route::get('/dashboard', fn () => redirect('/admin'))
    ->middleware(['auth', 'can:view-dashboard'])
    ->name('dashboard');



Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', fn () => redirect('/admin/reports'))->name('index');
        Route::get('/cashflow', fn () => redirect()->to(CashFlowReport::getUrl(request()->query())))
            ->middleware('can:view-cashflow-reports')
            ->name('cashflow');
        Route::get('/contributions', fn () => redirect()->to(ContributionReport::getUrl(request()->query())))
            ->middleware('can:view-contribution-reports')
            ->name('contributions');
});

Route::middleware(['auth', 'verified', 'can:manage-certificates'])->group(function () {
    Route::get('purok_certificates', fn () => redirect()->to(PurokCertificateResource::getUrl()))
        ->name('purok_certificates.index');

    Route::get('purok_certificates/create', fn () => redirect()->to(PurokCertificateResource::getUrl('create')))
        ->name('purok_certificates.create');

    Route::get('purok_certificates/{purok_certificate}/edit', fn (App\Models\PurokCertificate $purok_certificate) => redirect()->to(PurokCertificateResource::getUrl('edit', ['record' => $purok_certificate])))
        ->name('purok_certificates.edit');
});



require __DIR__.'/auth.php';
