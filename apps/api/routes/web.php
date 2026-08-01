<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvestmentAccountController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    $accounts = \App\Models\InvestmentAccount::query()
        ->where('user_id', $request->user()->id)
        ->get();

    return view('dashboard', [
        'accounts' => $accounts,
        'portfolioValue' => $accounts->sum('current_value'),
        'cashValue' => $accounts->sum('cash_value'),
        'accountCount' => $accounts->count(),
    ]);
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

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/accounts/{investmentAccount}/transactions',
        [\App\Http\Controllers\InvestmentTransactionController::class, 'index'],
    )->name('accounts.transactions.index');

    Route::get(
        '/accounts/{investmentAccount}/transactions/create',
        [\App\Http\Controllers\InvestmentTransactionController::class, 'create'],
    )->name('accounts.transactions.create');

    Route::post(
        '/accounts/{investmentAccount}/transactions',
        [\App\Http\Controllers\InvestmentTransactionController::class, 'store'],
    )->name('accounts.transactions.store');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/costs',
        [\App\Http\Controllers\CostAnalyticsController::class, 'index'],
    )->name('analytics.costs');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/fund-expenses',
        [\App\Http\Controllers\FundExpenseAnalyticsController::class, 'index'],
    )->name('analytics.fund-expenses');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/helm-score',
        [\App\Http\Controllers\HelmScoreController::class, 'index'],
    )->name('analytics.helm-score');
});
