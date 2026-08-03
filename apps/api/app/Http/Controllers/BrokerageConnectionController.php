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
            ->where('user_id', $request->user()->id)
            ->withCount('investmentAccounts')
            ->with([
                'investmentAccounts.institution',
            ])
            ->orderByDesc('created_at')
            ->get();

        return view(
            'brokerage-connections.index',
            [
                'connections' => $connections,
            ],
        );
    }

    public function create(): View
    {
        return view(
            'brokerage-connections.create',
        );
    }

    public function connect(
        Request $request,
        BrokerageProviderManager $manager,
    ): RedirectResponse {
        $validated = $request->validate([
            'provider' => [
                'required',
                'in:fake',
            ],

            'brokerage_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $provider = $manager->driver(
            $validated['provider'],
        );

        $provider->registerUser(
            $request->user(),
        );

        $connection = BrokerageConnection::query()->create([
            'user_id' => $request->user()->id,
            'provider' => $validated['provider'],
            'brokerage_name' =>
                $validated['brokerage_name']
                ?: 'Helmio Test Brokerage',
            'status' =>
                BrokerageConnection::STATUS_PENDING,
            'read_only' => true,
            'capabilities' => [
                'accounts',
                'positions',
                'transactions',
            ],
            'metadata' => [
                'created_from' =>
                    'connection_flow',
            ],
        ]);

        $url = $provider->createConnectionUrl(
            user: $request->user(),
            redirectUrl: route(
                'brokerage-connections.index',
            ),
            reconnect: $connection,
        );

        return redirect()->to($url);
    }

    public function fakeComplete(
        Request $request,
        BrokerageConnection $brokerageConnection,
        BrokerageSyncService $syncService,
    ): RedirectResponse {
        $this->authorizeConnection(
            $request,
            $brokerageConnection,
        );

        $brokerageConnection->update([
            'provider_connection_id' =>
                'fake-connection-'
                .$brokerageConnection->id,
            'status' =>
                BrokerageConnection::STATUS_ACTIVE,
            'connected_at' => now(),
            'last_error' => null,
        ]);

        try {
            $stats = $syncService->sync(
                $brokerageConnection,
            );

            return redirect()
                ->route(
                    'brokerage-connections.index',
                )
                ->with(
                    'success',
                    sprintf(
                        'Brokerage connected. Imported %d account, %d holdings and %d transactions.',
                        $stats['accounts'],
                        $stats['positions'],
                        $stats['transactions'],
                    ),
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'brokerage-connections.index',
                )
                ->with(
                    'error',
                    'The connection was created, but the first synchronization failed: '
                    .$exception->getMessage(),
                );
        }
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

        try {
            $stats = $syncService->sync(
                $brokerageConnection,
            );

            return back()->with(
                'success',
                sprintf(
                    'Synchronization complete: %d account, %d holdings and %d transactions.',
                    $stats['accounts'],
                    $stats['positions'],
                    $stats['transactions'],
                ),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Synchronization failed: '
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

        $provider = $manager->driver(
            $brokerageConnection->provider,
        );

        $provider->disconnect(
            $brokerageConnection,
        );

        return back()->with(
            'success',
            'Brokerage connection disconnected. Imported account data was retained.',
        );
    }

    private function authorizeConnection(
        Request $request,
        BrokerageConnection $brokerageConnection,
    ): void {
        abort_unless(
            $brokerageConnection->user_id
                === $request->user()->id,
            403,
        );
    }
}