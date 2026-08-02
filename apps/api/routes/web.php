<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvestmentAccountController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
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

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/diversification',
        [\App\Http\Controllers\DiversificationAnalyticsController::class, 'index'],
    )->name('analytics.diversification');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/trading-discipline',
        [\App\Http\Controllers\TradingDisciplineAnalyticsController::class, 'index'],
    )->name('analytics.trading-discipline');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/accounts/{investmentAccount}/performance-data',
        [\App\Http\Controllers\PerformanceDataController::class, 'index'],
    )->name('accounts.performance-data.index');

    Route::post(
        '/accounts/{investmentAccount}/portfolio-snapshots',
        [\App\Http\Controllers\PerformanceDataController::class, 'storeSnapshot'],
    )->name('accounts.portfolio-snapshots.store');

    Route::put(
        '/accounts/{investmentAccount}/benchmark',
        [\App\Http\Controllers\PerformanceDataController::class, 'assignBenchmark'],
    )->name('accounts.benchmark.update');

    Route::post(
        '/benchmarks',
        [\App\Http\Controllers\PerformanceDataController::class, 'storeBenchmark'],
    )->name('benchmarks.store');

    Route::post(
        '/benchmarks/{benchmark}/returns',
        [\App\Http\Controllers\PerformanceDataController::class, 'storeBenchmarkReturn'],
    )->name('benchmarks.returns.store');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/performance',
        [\App\Http\Controllers\PerformanceAnalyticsController::class, 'index'],
    )->name('analytics.performance');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/risk',
        [\App\Http\Controllers\RiskAnalyticsController::class, 'index'],
    )->name('analytics.risk');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/analytics/tax-efficiency',
        [\App\Http\Controllers\TaxEfficiencyAnalyticsController::class, 'index'],
    )->name('analytics.tax-efficiency');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get(
        '/advisor-audit',
        [\App\Http\Controllers\AdvisorAuditController::class, 'index'],
    )->name('advisor-audit.index');
});
