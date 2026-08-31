<?php

use App\Http\Controllers\Api\MobileAskHelmioController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAccountController;

/*
|--------------------------------------------------------------------------
| Default API User Route
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Helmio Mobile API
|--------------------------------------------------------------------------
*/

Route::prefix('mobile')
    ->group(function (): void {

        /*
         * Public mobile login
         */
        Route::post(
            '/login',
            [MobileAuthController::class, 'login'],
        );

        /*
         * Authenticated mobile routes
         */
        Route::middleware('auth:sanctum')
            ->group(function (): void {

                Route::get(
                    '/me',
                    [MobileAuthController::class, 'me'],
                );

                Route::get(
                    '/subscription',
                    [MobileAuthController::class, 'subscription'],
                );

                Route::get(
                    '/dashboard',
                    [MobileDashboardController::class, 'show'],
                );

                Route::get(
                    '/ask-helmio',
                    [MobileAskHelmioController::class, 'index'],
                );

                Route::post(
                    '/ask-helmio',
                    [MobileAskHelmioController::class, 'ask'],
                );

                Route::get(
                    '/ask-helmio/{conversation}',
                    [MobileAskHelmioController::class, 'show'],
                );

                Route::get(
                    '/ask-helmio/{conversation}/status',
                    [MobileAskHelmioController::class, 'status'],
                );

                Route::post(
                    '/logout',
                    [MobileAuthController::class, 'logout'],
                );
                Route::get('/accounts', [MobileAccountController::class, 'index']);
                Route::get('/accounts/{account}', [MobileAccountController::class, 'show']);
            });
    });