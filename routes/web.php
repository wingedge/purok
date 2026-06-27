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

    Route::get('members', [MemberController::class, 'index'])
        ->middleware('can:view-members')
        ->name('members.index');

    Route::get('members/create', [MemberController::class, 'create'])
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

    Route::get('members/{member}/edit', [MemberController::class, 'edit'])
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

    Route::resource('expenses', ExpenseController::class)
        ->middleware('can:manage-finances');

    Route::get('incomes/export', [IncomeController::class, 'export'])
        ->middleware('can:manage-finances')
        ->name('incomes.export');

    Route::post('incomes/import', [IncomeController::class, 'import'])
        ->middleware('can:manage-finances')
        ->name('incomes.import');

    Route::resource('incomes', IncomeController::class)
        ->middleware('can:manage-finances');

    Route::get('/contributions', [ContributionController::class, 'index'])->name('contributions.index');

    Route::post('/contributions', [ContributionController::class, 'store'])
        ->middleware('can:manage-contributions')
        ->name('contributions.store');

    Route::delete('/contributions', [ContributionController::class, 'destroy'])
        ->middleware('can:manage-contributions')
        ->name('contributions.destroy');

    Route::get('inventories', [InventoryController::class, 'index'])
        ->name('inventories.index');

    Route::get('inventories/create', [InventoryController::class, 'create'])
        ->middleware('can:manage-inventory')
        ->name('inventories.create');

    Route::post('inventories', [InventoryController::class, 'store'])
        ->middleware('can:manage-inventory')
        ->name('inventories.store');

    Route::get('inventories/{inventory}', [InventoryController::class, 'show'])
        ->name('inventories.show');

    Route::get('inventories/{inventory}/edit', [InventoryController::class, 'edit'])
        ->middleware('can:manage-inventory')
        ->name('inventories.edit');

    Route::match(['put', 'patch'], 'inventories/{inventory}', [InventoryController::class, 'update'])
        ->middleware('can:manage-inventory')
        ->name('inventories.update');

    Route::delete('inventories/{inventory}', [InventoryController::class, 'destroy'])
        ->middleware('can:manage-inventory')
        ->name('inventories.destroy');

    Route::get('rentals', [RentalController::class, 'index'])
        ->name('rentals.index');

    Route::get('rentals/export', [RentalController::class, 'export'])
        ->middleware('can:manage-rentals')
        ->name('rentals.export');

    Route::post('rentals/import', [RentalController::class, 'import'])
        ->middleware('can:manage-rentals')
        ->name('rentals.import');

    Route::get('rentals/create', [RentalController::class, 'create'])
        ->middleware('can:manage-rentals')
        ->name('rentals.create');

    Route::post('rentals', [RentalController::class, 'store'])
        ->middleware('can:manage-rentals')
        ->name('rentals.store');

    Route::get('rentals/{rental}/edit', [RentalController::class, 'edit'])
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
        Route::get('/', function () {return view('reports.index');})->name('index');
        Route::get('/cashflow', [CashFlowController::class, 'index'])
            ->middleware('can:view-cashflow-reports')
            ->name('cashflow');
        Route::get('/contributions', [CashFlowController::class, 'contributions'])
            ->middleware('can:view-contribution-reports')
            ->name('contributions');
});

Route::middleware(['auth', 'verified', 'can:manage-certificates'])->group(function () {
    Route::resource('purok_certificates', PurokCertificateController::class); 
});



require __DIR__.'/auth.php';
