<?php

namespace App\Providers;

use App\Contracts\AI\AiInsightProviderInterface;
use App\Contracts\AI\PortfolioChatProviderInterface;
use App\Services\AI\Providers\FakeAiInsightProvider;
use App\Services\AI\Providers\FakePortfolioChatProvider;
use App\Services\AI\Providers\OpenAiAiInsightProvider;
use App\Services\AI\Providers\OpenAiPortfolioChatProvider;
use App\Services\Brokerage\BrokerageProviderManager;
use App\Services\Brokerage\Providers\FakeBrokerageProvider;
use App\Services\Brokerage\Providers\SnapTradeBrokerageProvider;
use App\Services\Brokerage\SnapTrade\SnapTradeClientFactory;
use Illuminate\Support\ServiceProvider;
use SnapTrade\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        $this->app->bind(
            AiInsightProviderInterface::class,
            function (
                $app,
            ): AiInsightProviderInterface {
                $provider = (string) config(
                    'ai.portfolio_chat_provider',
                    'fake',
                );

                return match ($provider) {
                    'openai' =>
                        $app->make(
                            OpenAiAiInsightProvider::class,
                        ),

                    default =>
                        $app->make(
                            FakeAiInsightProvider::class,
                        ),
                };
            },
        );

        $this->app->bind(
            PortfolioChatProviderInterface::class,
            function (
                $app,
            ): PortfolioChatProviderInterface {
                $provider = (string) config(
                    'ai.portfolio_chat_provider',
                    'fake',
                );

                return match ($provider) {
                    'openai' =>
                        $app->make(
                            OpenAiPortfolioChatProvider::class,
                        ),

                    default =>
                        $app->make(
                            FakePortfolioChatProvider::class,
                        ),
                };
            },
        );

        $this->app->singleton(
            BrokerageProviderManager::class,
            fn (): BrokerageProviderManager =>
                new BrokerageProviderManager(),
        );

        /*
         * Register the generated SnapTrade SDK client only when it is
         * requested. Credentials are validated by the factory.
         */
        $this->app->singleton(
            Client::class,
            fn ($app): Client =>
                $app->make(
                    SnapTradeClientFactory::class,
                )->make(),
        );
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        $manager = $this->app->make(
            BrokerageProviderManager::class,
        );

        $manager->register(
            $this->app->make(
                FakeBrokerageProvider::class,
            ),
        );

        if (
            filled(
                config(
                    'services.snaptrade.client_id',
                ),
            )
            && filled(
                config(
                    'services.snaptrade.consumer_key',
                ),
            )
        ) {
            $manager->register(
                $this->app->make(
                    SnapTradeBrokerageProvider::class,
                ),
            );
        }
    }
}