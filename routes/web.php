<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
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
});

Route::middleware(['auth'])->group(function () {
    Route::get('/members/search', [PurokCertificateController::class, 'searchMembers'])->name('members.search');
    Route::resource('members', MemberController::class);
    Route::post('members/import', [MemberController::class, 'import'])->name('members.import');
    Route::resource('expenses', ExpenseController::class);
    Route::resource('incomes', IncomeController::class);
    Route::get('/contributions', [ContributionController::class, 'index'])->name('contributions.index');
    Route::post('/contributions', [ContributionController::class, 'store'])->name('contributions.store');
    Route::delete('/contributions', [ContributionController::class, 'destroy'])->name('contributions.destroy');

    Route::resource('inventories', InventoryController::class);
    Route::resource('rentals', RentalController::class);
    Route::patch('/rentals/{rental}/return', [RentalController::class, 'returnItem'])->name('rentals.return');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');



Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {return view('reports.index');})->name('index');
        Route::get('/cashflow', [CashFlowController::class, 'index'])->name('cashflow');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('purok_certificates', PurokCertificateController::class); 
});



require __DIR__.'/auth.php';
