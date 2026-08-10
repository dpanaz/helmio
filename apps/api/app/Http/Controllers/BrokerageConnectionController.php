<?php

namespace App\Http\Controllers;

use App\Models\BrokerageConnection;
use App\Services\Brokerage\BrokerageProviderManager;
use App\Services\Brokerage\BrokerageSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        /*
         * Defense in depth:
         * even if a bad request somehow reaches this point,
         * never allow the fake provider in production.
         */
        if (
            app()->environment('production')
            && $providerName === 'fake'
        ) {
            abort(404);
        }

        $provider = $manager->driver(
            $providerName,
        );

        $provider->registerUser(
            $request->user(),
        );

        /*
         * Capture the user's remote SnapTrade connections before
         * opening the connection portal. When the callback returns,
         * Helmio can compare this list against the new remote state.
         */
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

        if ($providerName === 'fake') {
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
            )
                ->filter()
                ->values();

            /*
             * SnapTrade may return the user from the Connection Portal
             * before Helmio can immediately distinguish the new remote
             * authorization from prior ones.
             *
             * Retry briefly before giving up.
             */
            $remoteConnections =
                $this->loadRemoteConnectionsWithRetry(
                    provider: $provider,
                    request: $request,
                );

            /*
             * Log safe metadata so we can diagnose provider timing or
             * connection matching problems without logging credentials.
             */
            logger()->info(
                'SnapTrade callback connection comparison',
                [
                    'user_id' =>
                        $request->user()->id,

                    'pending_connection_id' =>
                        $brokerageConnection->id,

                    'known_remote_ids' =>
                        $knownIds
                            ->values()
                            ->all(),

                    'returned_connections' =>
                        $remoteConnections
                            ->map(
                                fn (
                                    BrokerageConnection $candidate,
                                ): array => [
                                    'local_id' =>
                                        $candidate->id,

                                    'provider_connection_id' =>
                                        $candidate
                                            ->provider_connection_id,

                                    'status' =>
                                        $candidate->status,

                                    'brokerage_name' =>
                                        $candidate
                                            ->brokerage_name,

                                    'connected_at' =>
                                        $candidate
                                            ->connected_at
                                            ?->toIso8601String(),

                                    'updated_at' =>
                                        $candidate
                                            ->updated_at
                                            ?->toIso8601String(),
                                ],
                            )
                            ->values()
                            ->all(),
                ],
            );

            /*
             * First choice:
             * use a remote connection whose provider_connection_id
             * did not exist before the user opened SnapTrade.
             */
            $remote = $remoteConnections
                ->filter(
                    fn (
                        BrokerageConnection $candidate,
                    ): bool =>
                        filled(
                            $candidate
                                ->provider_connection_id,
                        )
                        && ! $knownIds->contains(
                            $candidate
                                ->provider_connection_id,
                        ),
                )
                ->sortByDesc(
                    fn (
                        BrokerageConnection $candidate,
                    ) =>
                        $this->connectionSortTimestamp(
                            $candidate,
                        ),
                )
                ->first();

            /*
             * Fallback:
             * some Connection Portal flows may return a usable
             * authorization without making the new ID immediately
             * distinguishable from the pre-portal list.
             *
             * In that case, take the newest valid non-error connection.
             */
            if ($remote === null) {
                $remote = $remoteConnections
                    ->filter(
                        fn (
                            BrokerageConnection $candidate,
                        ): bool =>
                            filled(
                                $candidate
                                    ->provider_connection_id,
                            )
                            && ! in_array(
                                $candidate->status,
                                [
                                    BrokerageConnection::STATUS_ERROR,
                                    BrokerageConnection::STATUS_PENDING,
                                ],
                                true,
                            ),
                    )
                    ->sortByDesc(
                        fn (
                            BrokerageConnection $candidate,
                        ) =>
                            $this->connectionSortTimestamp(
                                $candidate,
                            ),
                    )
                    ->first();
            }

            /*
             * Last fallback:
             * if SnapTrade returned a connection with a valid remote ID
             * but its local status mapping is still pending, use the
             * newest valid remote authorization rather than failing the
             * entire user flow.
             */
            if ($remote === null) {
                $remote = $remoteConnections
                    ->filter(
                        fn (
                            BrokerageConnection $candidate,
                        ): bool =>
                            filled(
                                $candidate
                                    ->provider_connection_id,
                            ),
                    )
                    ->sortByDesc(
                        fn (
                            BrokerageConnection $candidate,
                        ) =>
                            $this->connectionSortTimestamp(
                                $candidate,
                            ),
                    )
                    ->first();
            }

            if ($remote === null) {
                $brokerageConnection->update([
                    'status' =>
                        BrokerageConnection::STATUS_ERROR,

                    'last_error' =>
                        'SnapTrade returned from the connection portal, but no usable brokerage authorization was available.',
                ]);

                return $this->redirectAfterFailure(
                    $request,
                    'Your brokerage authorization completed, but Helmio could not retrieve the connected account yet. Please try again.',
                );
            }

            $brokerageConnection->update([
                'provider_connection_id' =>
                    $remote
                        ->provider_connection_id,

                'brokerage_name' =>
                    $remote->brokerage_name
                    ?: $brokerageConnection
                        ->brokerage_name,

                'brokerage_slug' =>
                    $remote->brokerage_slug,

                'status' =>
                    $remote->status
                    ?: BrokerageConnection::STATUS_ACTIVE,

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

                        'matched_provider_connection_id' =>
                            $remote
                                ->provider_connection_id,
                    ],
                ),
            ]);

            /*
             * listConnections() may materialize a temporary local
             * BrokerageConnection model representing the remote
             * authorization. Remove that duplicate after merging it
             * into the pending connection created by this flow.
             */
            if (
                $remote->id !== null
                && $remote->id
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
         * Never allow fake brokerage completion in production.
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
     * Retry SnapTrade connection retrieval briefly.
     *
     * Some brokerages complete authorization successfully but
     * SnapTrade's connection list may lag behind the browser
     * redirect by a fraction of a second.
     */
    private function loadRemoteConnectionsWithRetry(
        mixed $provider,
        Request $request,
    ): Collection {
        $attempts = 3;

        for (
            $attempt = 1;
            $attempt <= $attempts;
            $attempt++
        ) {
            $connections = $provider
                ->listConnections(
                    $request->user(),
                );

            if ($connections->isNotEmpty()) {
                return $connections;
            }

            if ($attempt < $attempts) {
                usleep(
                    500_000,
                );
            }
        }

        return collect();
    }

    /**
     * Produce a sortable timestamp for remote connections.
     */
    private function connectionSortTimestamp(
        BrokerageConnection $connection,
    ): int {
        $timestamp =
            $connection->connected_at
            ?? $connection->updated_at
            ?? $connection->created_at;

        return $timestamp
            ? $timestamp->getTimestamp()
            : 0;
    }

    /**
     * Return brokerage providers permitted in the current
     * application environment.
     *
     * FakeBrokerageProvider remains available locally and in
     * automated tests, but it is never exposed in production.
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
                fn (
                    string $provider,
                ): bool =>
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