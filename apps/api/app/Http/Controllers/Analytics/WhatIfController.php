<?php

namespace App\Http\Controllers\Analytics;

use App\Data\Simulation\PortfolioChangeData;
use App\Data\Simulation\SimulatedHoldingData;
use App\Http\Controllers\Controller;
use App\Models\Security;
use App\Services\MarketData\SecurityPriceResolver;
use App\Services\Simulation\PortfolioSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class WhatIfController extends Controller
{
    public function index(
        Request $request,
        PortfolioSimulationService $simulationService,
    ): View {
        $portfolio = $simulationService->baseline(
            $request->user()
        );

        return view(
            'analytics.what-if.index',
            [
                'portfolio' =>
                    $portfolio,
            ],
        );
    }

    public function simulate(
        Request $request,
        PortfolioSimulationService $simulationService,
        SecurityPriceResolver $securityPriceResolver,
    ): JsonResponse {
        $validated = $request->validate([
            'changes' => [
                'required',
                'array',
                'min:1',
            ],

            'changes.*.action' => [
                'required',
                'string',
                Rule::in(
                    PortfolioChangeData::allowedActions()
                ),
            ],

            'changes.*.symbol' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9.\-]+$/',
            ],

            'changes.*.amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'changes.*.percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1',
            ],

            'changes.*.advisory_fee_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1',
            ],
        ]);

        try {
            $changes = collect(
                $validated['changes']
            )
                ->map(
                    fn (array $change) =>
                        $this->makeChange(
                            $change,
                            $securityPriceResolver,
                        )
                )
                ->all();

            $result =
                $simulationService->simulate(
                    $request->user(),
                    $changes,
                );

            return response()->json([
                'success' =>
                    true,

                'baseline' =>
                    $this->portfolioPayload(
                        $result['baseline']
                    ),

                'simulated' =>
                    $this->portfolioPayload(
                        $result['simulated']
                    ),

                'comparison' =>
                    $result['comparison'],

                'analytics' =>
                    $result['analytics'],

                'production_analytics' =>
                    $result['production_analytics'],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),
            ], 422);
        }
    }

    private function makeChange(
        array $change,
        SecurityPriceResolver $securityPriceResolver,
    ): PortfolioChangeData {
        $securityData = null;

        if (
            $change['action']
            === PortfolioChangeData::ACTION_BUY
        ) {
            $symbol = strtoupper(
                trim(
                    (string) (
                        $change['symbol']
                        ?? ''
                    )
                )
            );

            if ($symbol === '') {
                throw new InvalidArgumentException(
                    'A security symbol is required.'
                );
            }

            $security = Security::query()
                ->whereRaw(
                    'UPPER(symbol) = ?',
                    [$symbol]
                )
                ->first();

            if (! $security) {
                throw new InvalidArgumentException(
                    "Helmio does not have security data for {$symbol} yet."
                );
            }

            $price =
                $securityPriceResolver->resolve(
                    $security
                );

            if ($price <= 0) {
                throw new InvalidArgumentException(
                    "Helmio found {$symbol}, but no usable price or NAV is available yet."
                );
            }

            $securityData =
                new SimulatedHoldingData(
                    securityId:
                        $security->id,

                    symbol:
                        strtoupper(
                            $security->symbol
                        ),

                    name:
                        $security->name
                        ?? $security->symbol,

                    quantity:
                        0,

                    price:
                        $price,

                    marketValue:
                        0,

                    assetClass:
                        data_get(
                            $security,
                            'asset_class'
                        ),

                    sector:
                        data_get(
                            $security,
                            'sector'
                        ),

                    expenseRatio:
                        $this->nullableFloat(
                            data_get(
                                $security,
                                'expense_ratio'
                            )
                        ),

                    costBasis:
                        null,

                    accountId:
                        null,

                    accountType:
                        null,
                );
        }

        return new PortfolioChangeData(
            action:
                $change['action'],

            symbol:
                isset(
                    $change['symbol']
                )
                    ? strtoupper(
                        trim(
                            $change['symbol']
                        )
                    )
                    : null,

            amount:
                isset(
                    $change['amount']
                )
                    ? (float) $change['amount']
                    : null,

            percentage:
                isset(
                    $change['percentage']
                )
                    ? (float) $change['percentage']
                    : null,

            advisoryFeeRate:
                isset(
                    $change[
                        'advisory_fee_rate'
                    ]
                )
                    ? (float) $change[
                        'advisory_fee_rate'
                    ]
                    : null,

            security:
                $securityData,
        );
    }

    private function nullableFloat(
        mixed $value,
    ): ?float {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return (float) $value;
    }

    private function portfolioPayload(
        mixed $portfolio,
    ): array {
        $totalValue =
            $portfolio->totalValue();

        return [
            'total_value' =>
                $totalValue,

            'holdings_value' =>
                $portfolio->holdingsValue(),

            'cash' =>
                $portfolio->cash,

            'advisory_fee_rate' =>
                $portfolio->advisoryFeeRate,

            'holdings' =>
                $portfolio->holdings
                    ->map(
                        fn ($holding) => [
                            'security_id' =>
                                $holding->securityId,

                            'symbol' =>
                                $holding->symbol,

                            'name' =>
                                $holding->name,

                            'quantity' =>
                                $holding->quantity,

                            'price' =>
                                $holding->price,

                            'market_value' =>
                                $holding->marketValue,

                            'weight' =>
                                $holding->weight(
                                    $totalValue
                                ),

                            'asset_class' =>
                                $holding->assetClass,

                            'sector' =>
                                $holding->sector,

                            'expense_ratio' =>
                                $holding->expenseRatio,

                            'cost_basis' =>
                                $holding->costBasis,

                            'account_id' =>
                                $holding->accountId,

                            'account_type' =>
                                $holding->accountType,
                        ]
                    )
                    ->values(),
        ];
    }
}