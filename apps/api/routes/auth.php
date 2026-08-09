<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    |
    | Public registration can be enabled or disabled with:
    |
    | HELMIO_REGISTRATION_OPEN=true
    |
    | When registration is disabled:
    | - GET /register redirects to the homepage
    | - POST /register is blocked
    |
    */

    Route::get(
        'register',
        function () {
            if (! config('app.registration_open')) {
                return redirect('/')
                    ->with(
                        'registration_closed',
                        'Helmio is launching soon. Registration is not open yet.'
                    );
            }

            return app(
                RegisteredUserController::class
            )->create();
        }
    )->name('register');


    Route::post(
        'register',
        function (Request $request) {
            abort_unless(
                config('app.registration_open'),
                403,
                'Helmio registration is not open yet.'
            );

            return app(
                RegisteredUserController::class
            )->store($request);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Route::get(
        'login',
        [
            AuthenticatedSessionController::class,
            'create',
        ],
    )->name('login');


    Route::post(
        'login',
        [
            AuthenticatedSessionController::class,
            'store',
        ],
    );


    /*
    |--------------------------------------------------------------------------
    | Forgot Password
    |--------------------------------------------------------------------------
    */

    Route::get(
        'forgot-password',
        [
            PasswordResetLinkController::class,
            'create',
        ],
    )->name('password.request');


    Route::post(
        'forgot-password',
        [
            PasswordResetLinkController::class,
            'store',
        ],
    )->name('password.email');


    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    Route::get(
        'reset-password/{token}',
        [
            NewPasswordController::class,
            'create',
        ],
    )->name('password.reset');


    Route::post(
        'reset-password',
        [
            NewPasswordController::class,
            'store',
        ],
    )->name('password.store');
});


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    */

    Route::get(
        'verify-email',
        EmailVerificationPromptController::class,
    )->name('verification.notice');


    Route::get(
        'verify-email/{id}/{hash}',
        VerifyEmailController::class,
    )
        ->middleware([
            'signed',
            'throttle:6,1',
        ])
        ->name('verification.verify');


    Route::post(
        'email/verification-notification',
        [
            EmailVerificationNotificationController::class,
            'store',
        ],
    )
        ->middleware('throttle:6,1')
        ->name('verification.send');


    /*
    |--------------------------------------------------------------------------
    | Confirm Password
    |--------------------------------------------------------------------------
    */

    Route::get(
        'confirm-password',
        [
            ConfirmablePasswordController::class,
            'show',
        ],
    )->name('password.confirm');


    Route::post(
        'confirm-password',
        [
            ConfirmablePasswordController::class,
            'store',
        ],
    );


    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    Route::put(
        'password',
        [
            PasswordController::class,
            'update',
        ],
    )->name('password.update');


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post(
        'logout',
        [
            AuthenticatedSessionController::class,
            'destroy',
        ],
    )->name('logout');
});