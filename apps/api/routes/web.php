<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvestmentAccountController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/accounts', [\App\Http\Controllers\InvestmentAccountController::class, 'index'])
        ->name('accounts.index');

    Route::get('/accounts/connect', [\App\Http\Controllers\InvestmentAccountController::class, 'create'])
        ->name('accounts.create');

    Route::post('/accounts', [\App\Http\Controllers\InvestmentAccountController::class, 'store'])
        ->name('accounts.store');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/accounts/{investmentAccount}/holdings',
        [\App\Http\Controllers\HoldingController::class, 'index'],
    )->name('accounts.holdings.index');

    Route::get(
        '/accounts/{investmentAccount}/holdings/create',
        [\App\Http\Controllers\HoldingController::class, 'create'],
    )->name('accounts.holdings.create');

    Route::post(
        '/accounts/{investmentAccount}/holdings',
        [\App\Http\Controllers\HoldingController::class, 'store'],
    )->name('accounts.holdings.store');
});
