<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
{
    $this->app->singleton(
        \App\Services\Brokerage\BrokerageProviderManager::class,
        function ($app) {
            $manager =
                new \App\Services\Brokerage\BrokerageProviderManager();

            $manager->register(
                $app->make(
                    \App\Services\Brokerage\Providers\FakeBrokerageProvider::class,
                ),
            );

            return $manager;
        },
    );
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
