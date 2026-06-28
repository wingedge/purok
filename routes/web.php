<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\ContributionController;
use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\Incomes\IncomeResource;
use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\PurokCertificates\PurokCertificateResource;
use App\Filament\Resources\Rentals\RentalResource;
use App\Http\Controllers\PurokCertificateController;
use App\Http\Controllers\Reports\CashFlowController;

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
    Route::get('/members/search', [PurokCertificateController::class, 'searchMembers'])
        ->middleware('can:manage-certificates')
        ->name('members.search');

    Route::get('members', fn () => redirect()->to(MemberResource::getUrl()))
        ->middleware('can:view-members')
        ->name('members.index');

    Route::get('members/create', fn () => redirect()->to(MemberResource::getUrl('create')))
        ->middleware('can:manage-members')
        ->name('members.create');

    Route::post('members/import', [MemberController::class, 'import'])
        ->middleware('can:manage-members')
        ->name('members.import');

    Route::get('members/export', [MemberController::class, 'export'])
        ->middleware('can:manage-members')
        ->name('members.export');

    Route::post('members', [MemberController::class, 'store'])
        ->middleware('can:manage-members')
        ->name('members.store');

    Route::get('members/{member}', [MemberController::class, 'show'])
        ->middleware('can:view-members')
        ->name('members.show');

    Route::get('members/{member}/edit', fn (App\Models\Member $member) => redirect()->to(MemberResource::getUrl('edit', ['record' => $member])))
        ->middleware('can:manage-members')
        ->name('members.edit');

    Route::match(['put', 'patch'], 'members/{member}', [MemberController::class, 'update'])
        ->middleware('can:manage-members')
        ->name('members.update');

    Route::delete('members/{member}', [MemberController::class, 'destroy'])
        ->middleware('can:delete-members')
        ->name('members.destroy');

    Route::get('expenses/export', [ExpenseController::class, 'export'])
        ->middleware('can:manage-finances')
        ->name('expenses.export');

    Route::post('expenses/import', [ExpenseController::class, 'import'])
        ->middleware('can:manage-finances')
        ->name('expenses.import');

    Route::get('expenses', fn () => redirect()->to(ExpenseResource::getUrl()))
        ->middleware('can:manage-finances')
        ->name('expenses.index');

    Route::get('expenses/create', fn () => redirect()->to(ExpenseResource::getUrl('create')))
        ->middleware('can:manage-finances')
        ->name('expenses.create');

    Route::get('expenses/{expense}/edit', fn (App\Models\Expense $expense) => redirect()->to(ExpenseResource::getUrl('edit', ['record' => $expense])))
        ->middleware('can:manage-finances')
        ->name('expenses.edit');

    Route::resource('expenses', ExpenseController::class)
        ->except(['index', 'create', 'edit'])
        ->middleware('can:manage-finances');

    Route::get('incomes/export', [IncomeController::class, 'export'])
        ->middleware('can:manage-finances')
        ->name('incomes.export');

    Route::post('incomes/import', [IncomeController::class, 'import'])
        ->middleware('can:manage-finances')
        ->name('incomes.import');

    Route::get('incomes', fn () => redirect()->to(IncomeResource::getUrl()))
        ->middleware('can:manage-finances')
        ->name('incomes.index');

    Route::get('incomes/create', fn () => redirect()->to(IncomeResource::getUrl('create')))
        ->middleware('can:manage-finances')
        ->name('incomes.create');

    Route::get('incomes/{income}/edit', fn (App\Models\Income $income) => redirect()->to(IncomeResource::getUrl('edit', ['record' => $income])))
        ->middleware('can:manage-finances')
        ->name('incomes.edit');

    Route::resource('incomes', IncomeController::class)
        ->except(['index', 'create', 'edit'])
        ->middleware('can:manage-finances');

    Route::get('/contributions', fn () => redirect('/admin/contribution-grid'))->name('contributions.index');

    Route::post('/contributions', [ContributionController::class, 'store'])
        ->middleware('can:manage-contributions')
        ->name('contributions.store');

    Route::delete('/contributions', [ContributionController::class, 'destroy'])
        ->middleware('can:manage-contributions')
        ->name('contributions.destroy');

    Route::get('inventories', fn () => redirect()->to(InventoryResource::getUrl()))
        ->middleware('can:manage-inventory')
        ->name('inventories.index');

    Route::get('inventories/create', fn () => redirect()->to(InventoryResource::getUrl('create')))
        ->middleware('can:manage-inventory')
        ->name('inventories.create');

    Route::post('inventories', [InventoryController::class, 'store'])
        ->middleware('can:manage-inventory')
        ->name('inventories.store');

    Route::get('inventories/{inventory}', [InventoryController::class, 'show'])
        ->name('inventories.show');

    Route::get('inventories/{inventory}/edit', fn (App\Models\Inventory $inventory) => redirect()->to(InventoryResource::getUrl('edit', ['record' => $inventory])))
        ->middleware('can:manage-inventory')
        ->name('inventories.edit');

    Route::match(['put', 'patch'], 'inventories/{inventory}', [InventoryController::class, 'update'])
        ->middleware('can:manage-inventory')
        ->name('inventories.update');

    Route::delete('inventories/{inventory}', [InventoryController::class, 'destroy'])
        ->middleware('can:manage-inventory')
        ->name('inventories.destroy');

    Route::get('rentals', fn () => redirect()->to(RentalResource::getUrl()))
        ->middleware('can:manage-rentals')
        ->name('rentals.index');

    Route::get('rentals/export', [RentalController::class, 'export'])
        ->middleware('can:manage-rentals')
        ->name('rentals.export');

    Route::post('rentals/import', [RentalController::class, 'import'])
        ->middleware('can:manage-rentals')
        ->name('rentals.import');

    Route::get('rentals/create', fn () => redirect()->to(RentalResource::getUrl('create')))
        ->middleware('can:manage-rentals')
        ->name('rentals.create');

    Route::post('rentals', [RentalController::class, 'store'])
        ->middleware('can:manage-rentals')
        ->name('rentals.store');

    Route::get('rentals/{rental}/edit', fn (App\Models\Rental $rental) => redirect()->to(RentalResource::getUrl('edit', ['record' => $rental])))
        ->middleware('can:manage-rentals')
        ->name('rentals.edit');

    Route::match(['put', 'patch'], 'rentals/{rental}', [RentalController::class, 'update'])
        ->middleware('can:manage-rentals')
        ->name('rentals.update');

    Route::delete('rentals/{rental}', [RentalController::class, 'destroy'])
        ->middleware('can:manage-rentals')
        ->name('rentals.destroy');

    Route::patch('/rentals/{rental}/return', [RentalController::class, 'returnItem'])
        ->middleware('can:manage-rentals')
        ->name('rentals.return');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'can:view-dashboard'])
    ->name('dashboard');



Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', fn () => redirect('/admin/reports'))->name('index');
        Route::get('/cashflow', [CashFlowController::class, 'index'])
            ->middleware('can:view-cashflow-reports')
            ->name('cashflow');
        Route::get('/contributions', [CashFlowController::class, 'contributions'])
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

    Route::resource('purok_certificates', PurokCertificateController::class)
        ->except(['index', 'create', 'edit']);
});



require __DIR__.'/auth.php';
