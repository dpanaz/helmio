<?php

namespace App\Http\Controllers;

use App\Models\BrokerageConnection;
use App\Services\Brokerage\BrokerageProviderManager;
use App\Services\Brokerage\BrokerageSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class BrokerageConnectionController extends Controller
{
    public function index(
        Request $request,
    ): View {
        $connections = BrokerageConnection::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->withCount(
                'investmentAccounts',
            )
            ->with([
                'investmentAccounts.institution',

                'syncRuns' => fn ($query) =>
                    $query
                        ->latest('started_at')
                        ->limit(5),
            ])
            ->orderByDesc(
                'created_at',
            )
            ->get();

        return view(
            'brokerage-connections.index',
            [
                'connections' =>
                    $connections,
            ],
        );
    }

    public function create(
        Request $request,
        BrokerageProviderManager $manager,
    ): View {
        if ($request->boolean('onboarding')) {
            $request->session()->put(
                'brokerage_onboarding',
                true,
            );
        }

        return view(
            'brokerage-connections.create',
            [
                'providers' =>
                    $this->availableProviders(
                        $manager,
                    ),

                'isOnboarding' =>
                    $this->isOnboarding(
                        $request,
                    ),
            ],
        );
    }

    public function connect(
        Request $request,
        BrokerageProviderManager $manager,
    ): RedirectResponse {
        /*
         * Only providers allowed in the current environment
         * may be submitted.
         *
         * This prevents the fake provider from being used in
         * production even if someone manually submits the
         * provider value.
         */
        $providers = $this->availableProviders(
            $manager,
        );

        $validated = $request->validate([
            'provider' => [
                'required',
                'string',
                'in:'.implode(
                    ',',
                    $providers,
                ),
            ],

            'brokerage_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'brokerage_slug' => [
                'nullable',
                'string',
                'max:100',
            ],

            'onboarding' => [
                'nullable',
                'boolean',
            ],
        ]);

        if ($request->boolean('onboarding')) {
            $request->session()->put(
                'brokerage_onboarding',
                true,
            );
        }

        $providerName =
            $validated['provider'];

        $provider = $manager->driver(
            $providerName,
        );

        $provider->registerUser(
            $request->user(),
        );

        $existingRemoteIds =
            $providerName === 'snaptrade'
                ? $provider
                    ->listConnections(
                        $request->user(),
                    )
                    ->pluck(
                        'provider_connection_id',
                    )
                    ->filter()
                    ->values()
                    ->all()
                : [];

        $connection = BrokerageConnection::query()
            ->create([
                'user_id' =>
                    $request->user()->id,

                'provider' =>
                    $providerName,

                'brokerage_name' =>
                    $validated['brokerage_name']
                    ?: (
                        $providerName === 'snaptrade'
                            ? 'Brokerage connection'
                            : 'Helmio Test Brokerage'
                    ),

                'brokerage_slug' =>
                    $validated['brokerage_slug']
                    ?? null,

                'status' =>
                    BrokerageConnection::STATUS_PENDING,

                'read_only' =>
                    true,

                'capabilities' => [
                    'accounts',
                    'positions',
                    'transactions',
                ],

                'metadata' => [
                    'created_from' =>
                        $this->isOnboarding($request)
                            ? 'onboarding'
                            : 'connection_flow',

                    'onboarding' =>
                        $this->isOnboarding(
                            $request,
                        ),

                    'remote_connections_before' =>
                        $existingRemoteIds,
                ],
            ]);

        /*
         * Fake brokerage connections are available only
         * outside production.
         */
        if ($providerName === 'fake') {
            abort_if(
                app()->environment('production'),
                404,
            );

            return redirect()->to(
                $provider->createConnectionUrl(
                    user: $request->user(),

                    redirectUrl:
                        $this->isOnboarding($request)
                            ? route(
                                'onboarding.syncing',
                            )
                            : route(
                                'brokerage-connections.index',
                            ),

                    reconnect: $connection,
                ),
            );
        }

        $url = $provider->createConnectionUrl(
            user: $request->user(),

            redirectUrl: route(
                'brokerage-connections.callback',
                $connection,
            ),

            brokerageSlug:
                $validated['brokerage_slug']
                ?? null,
        );

        return redirect()->away(
            $url,
        );
    }

    public function callback(
        Request $request,
        BrokerageConnection $brokerageConnection,
        BrokerageProviderManager $manager,
        BrokerageSyncService $syncService,
    ): RedirectResponse {
        $this->authorizeConnection(
            $request,
            $brokerageConnection,
        );

        abort_unless(
            $brokerageConnection->provider
                === 'snaptrade',
            404,
        );

        try {
            $provider = $manager->driver(
                'snaptrade',
            );

            $knownIds = collect(
                data_get(
                    $brokerageConnection,
                    'metadata.remote_connections_before',
                    [],
                ),
            )->filter();

            $remoteConnections =
                $provider->listConnections(
                    $request->user(),
                );

            $remote = $remoteConnections
                ->filter(
                    fn (
                        BrokerageConnection $candidate,
                    ): bool =>
                        $candidate
                            ->provider_connection_id
                            !== null
                        && ! $knownIds->contains(
                            $candidate
                                ->provider_connection_id,
                        ),
                )
                ->sortByDesc('id')
                ->first();

            $remote ??= $remoteConnections
                ->filter(
                    fn (
                        BrokerageConnection $candidate,
                    ): bool =>
                        $candidate->id
                            !== $brokerageConnection->id
                        && $candidate
                            ->provider_connection_id
                            !== null,
                )
                ->sortByDesc('id')
                ->first();

            if ($remote === null) {
                $brokerageConnection->update([
                    'status' =>
                        BrokerageConnection::STATUS_ERROR,

                    'last_error' =>
                        'No new SnapTrade connection was found.',
                ]);

                return $this->redirectAfterFailure(
                    $request,
                    'No new brokerage connection was found. Please try again.',
                );
            }

            $brokerageConnection->update([
                'provider_connection_id' =>
                    $remote->provider_connection_id,

                'brokerage_name' =>
                    $remote->brokerage_name
                    ?: $brokerageConnection
                        ->brokerage_name,

                'brokerage_slug' =>
                    $remote->brokerage_slug,

                'status' =>
                    $remote->status,

                'connected_at' =>
                    $remote->connected_at
                    ?: now(),

                'last_error' =>
                    null,

                'capabilities' =>
                    $remote->capabilities
                    ?: $brokerageConnection
                        ->capabilities,

                'metadata' => array_merge(
                    $brokerageConnection
                        ->metadata
                        ?? [],
                    [
                        'snaptrade_connection' =>
                            $remote->metadata,

                        'callback_completed_at' =>
                            now()->toIso8601String(),
                    ],
                ),
            ]);

            if (
                $remote->id
                !== $brokerageConnection->id
            ) {
                $remote->delete();
            }

            $stats = $syncService->sync(
                $brokerageConnection->fresh(),
                'connection',
            );

            $message = sprintf(
                'Brokerage connected. Imported %d account(s), %d holding(s), and %d transaction(s).',
                $stats['accounts'],
                $stats['positions'],
                $stats['transactions'],
            );

            return $this->redirectAfterSuccess(
                $request,
                $message,
            );
        } catch (Throwable $exception) {
            report(
                $exception,
            );

            $brokerageConnection->update([
                'status' =>
                    BrokerageConnection::STATUS_ERROR,

                'last_error' =>
                    $exception->getMessage(),
            ]);

            return $this->redirectAfterFailure(
                $request,
                'The connection returned, but synchronization failed: '
                .$exception->getMessage(),
            );
        }
    }

    public function fakeComplete(
        Request $request,
        BrokerageConnection $brokerageConnection,
        BrokerageSyncService $syncService,
    ): RedirectResponse {
        /*
         * Fake brokerage completion must never run in
         * production even if someone discovers the route.
         */
        abort_if(
            app()->environment('production'),
            404,
        );

        $this->authorizeConnection(
            $request,
            $brokerageConnection,
        );

        if (
            data_get(
                $brokerageConnection,
                'metadata.onboarding',
                false,
            )
        ) {
            $request->session()->put(
                'brokerage_onboarding',
                true,
            );
        }

        $brokerageConnection->update([
            'provider_connection_id' =>
                'fake-connection-'
                .$brokerageConnection->id,

            'status' =>
                BrokerageConnection::STATUS_ACTIVE,

            'connected_at' =>
                now(),

            'last_error' =>
                null,
        ]);

        return $this->runSync(
            request: $request,
            connection: $brokerageConnection,
            syncService: $syncService,
            trigger: 'connection',
        );
    }

    public function sync(
        Request $request,
        BrokerageConnection $brokerageConnection,
        BrokerageSyncService $syncService,
    ): RedirectResponse {
        $this->authorizeConnection(
            $request,
            $brokerageConnection,
        );

        return $this->runSync(
            request: $request,
            connection: $brokerageConnection,
            syncService: $syncService,
            trigger: 'manual',
        );
    }

    public function refresh(
        Request $request,
        BrokerageConnection $brokerageConnection,
        BrokerageProviderManager $manager,
    ): RedirectResponse {
        $this->authorizeConnection(
            $request,
            $brokerageConnection,
        );

        try {
            $manager
                ->driver(
                    $brokerageConnection->provider,
                )
                ->requestRefresh(
                    $brokerageConnection,
                );

            return back()->with(
                'success',
                'The brokerage refresh was requested.',
            );
        } catch (Throwable $exception) {
            report(
                $exception,
            );

            return back()->with(
                'error',
                'Refresh request failed: '
                .$exception->getMessage(),
            );
        }
    }

    public function disconnect(
        Request $request,
        BrokerageConnection $brokerageConnection,
        BrokerageProviderManager $manager,
    ): RedirectResponse {
        $this->authorizeConnection(
            $request,
            $brokerageConnection,
        );

        try {
            $manager
                ->driver(
                    $brokerageConnection->provider,
                )
                ->disconnect(
                    $brokerageConnection,
                );

            return back()->with(
                'success',
                'Brokerage connection disconnected. Imported data was retained.',
            );
        } catch (Throwable $exception) {
            report(
                $exception,
            );

            return back()->with(
                'error',
                'Disconnect failed: '
                .$exception->getMessage(),
            );
        }
    }

    private function runSync(
        Request $request,
        BrokerageConnection $connection,
        BrokerageSyncService $syncService,
        string $trigger,
    ): RedirectResponse {
        try {
            $stats = $syncService->sync(
                $connection,
                $trigger,
            );

            $message = sprintf(
                'Synchronization complete: %d account(s), %d holding(s), and %d transaction(s).',
                $stats['accounts'],
                $stats['positions'],
                $stats['transactions'],
            );

            return $this->redirectAfterSuccess(
                $request,
                $message,
            );
        } catch (Throwable $exception) {
            report(
                $exception,
            );

            return $this->redirectAfterFailure(
                $request,
                'Synchronization failed: '
                .$exception->getMessage(),
            );
        }
    }

    /**
     * Return brokerage providers that are allowed in
     * the current application environment.
     *
     * The fake provider remains available for local
     * development and testing but is never exposed
     * in production.
     *
     * @return array<int, string>
     */
    private function availableProviders(
        BrokerageProviderManager $manager,
    ): array {
        $providers = collect(
            $manager->availableProviders(),
        );

        if (app()->environment('production')) {
            $providers = $providers->reject(
                fn (string $provider): bool =>
                    $provider === 'fake',
            );
        }

        return $providers
            ->values()
            ->all();
    }

    private function redirectAfterSuccess(
        Request $request,
        string $message,
    ): RedirectResponse {
        if (
            $request->session()->pull(
                'brokerage_onboarding',
                false,
            )
        ) {
            return redirect()
                ->route(
                    'onboarding.syncing',
                )
                ->with(
                    'success',
                    $message,
                );
        }

        return redirect()
            ->route(
                'brokerage-connections.index',
            )
            ->with(
                'success',
                $message,
            );
    }

    private function redirectAfterFailure(
        Request $request,
        string $message,
    ): RedirectResponse {
        if ($this->isOnboarding($request)) {
            return redirect()
                ->route(
                    'onboarding.connect',
                )
                ->with(
                    'error',
                    $message,
                );
        }

        return redirect()
            ->route(
                'brokerage-connections.index',
            )
            ->with(
                'error',
                $message,
            );
    }

    private function isOnboarding(
        Request $request,
    ): bool {
        return $request->boolean(
            'onboarding',
        )
            || filter_var(
                $request->session()->get(
                    'brokerage_onboarding',
                    false,
                ),
                FILTER_VALIDATE_BOOL,
            );
    }

    private function authorizeConnection(
        Request $request,
        BrokerageConnection $brokerageConnection,
    ): void {
        abort_unless(
            (int) $brokerageConnection->user_id
                === (int) $request->user()->id,
            403,
        );
    }
}