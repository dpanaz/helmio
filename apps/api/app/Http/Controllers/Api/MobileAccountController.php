<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvestmentAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileAccountController extends Controller
{
    public function index(
        Request $request,
    ): JsonResponse {
        $accounts = InvestmentAccount::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->with([
                'institution',
                'holdings.security',
            ])
            ->orderBy('name')
            ->get();

        $accountData = $accounts
            ->map(
                fn (InvestmentAccount $account): array =>
                    $this->accountData(
                        $account,
                        includeHoldings: false,
                    ),
            )
            ->values()
            ->all();

        return response()->json([
            'accounts' => $accountData,

            'summary' => [
                'account_count' =>
                    $accounts->count(),

                'total_value' =>
                    round(
                        $accounts->sum(
                            fn (
                                InvestmentAccount $account,
                            ): float =>
                                $this->accountValue(
                                    $account,
                                ),
                        ),
                        2,
                    ),
            ],
        ]);
    }

    public function show(
        Request $request,
        InvestmentAccount $account,
    ): JsonResponse {
        /*
         * Critical security check:
         * never allow one user to retrieve another
         * user's investment account.
         */
        abort_unless(
            $account->user_id ===
                $request->user()->id,
            404,
        );

        $account->load([
            'institution',
            'holdings.security',
        ]);

        return response()->json([
            'account' =>
                $this->accountData(
                    $account,
                    includeHoldings: true,
                ),
        ]);
    }

    private function accountData(
        InvestmentAccount $account,
        bool $includeHoldings = true,
    ): array {
        $value =
            $this->accountValue($account);

        $holdingsValue =
            (float) $account
                ->holdings
                ->sum(
                    fn ($holding): float =>
                        max(
                            0,
                            (float) (
                                $holding->market_value
                                ?? 0
                            ),
                        ),
                );

        $cashValue =
            (float) (
                $account->cash_value
                ?? 0
            );

        $data = [
            'id' =>
                $account->id,

            'name' =>
                $account->name
                ?: 'Investment Account',

            'account_type' =>
                $account->account_type,

            'account_number_mask' =>
                $account->account_number_mask,

            'institution' => [
                'id' =>
                    $account->institution?->id,

                'name' =>
                    $account->institution?->name,
            ],

            'value' =>
                round($value, 2),

            'holdings_value' =>
                round(
                    $holdingsValue,
                    2,
                ),

            'cash_value' =>
                round(
                    $cashValue,
                    2,
                ),

            'holding_count' =>
                $account->holdings->count(),

            'provider' =>
                $account->provider,

            'provider_synced_at' =>
                $account
                    ->provider_synced_at
                    ?->toIso8601String(),

            'updated_at' =>
                $account
                    ->updated_at
                    ?->toIso8601String(),
        ];

        if ($includeHoldings) {
            $data['holdings'] =
                $account->holdings
                    ->filter(
                        fn ($holding): bool =>
                            (float) (
                                $holding->market_value
                                ?? 0
                            ) > 0,
                    )
                    ->sortByDesc(
                        fn ($holding): float =>
                            (float) (
                                $holding->market_value
                                ?? 0
                            ),
                    )
                    ->map(
                        function ($holding) use (
                            $value,
                        ): array {
                            $marketValue =
                                (float) (
                                    $holding
                                        ->market_value
                                    ?? 0
                                );

                            $quantity =
                                (float) (
                                    $holding
                                        ->quantity
                                    ?? 0
                                );

                            $price =
                                $holding->price
                                !== null
                                    ? (float)
                                        $holding
                                            ->price
                                    : (
                                        $quantity > 0
                                            ? $marketValue
                                                / $quantity
                                            : null
                                    );

                            return [
                                'id' =>
                                    $holding->id,

                                'security_id' =>
                                    $holding
                                        ->security_id,

                                'symbol' =>
                                    $holding
                                        ->security
                                        ?->symbol,

                                'name' =>
                                    $holding
                                        ->security
                                        ?->name,

                                'security_type' =>
                                    $holding
                                        ->security
                                        ?->security_type,

                                'asset_class' =>
                                    $holding
                                        ->security
                                        ?->asset_class,

                                'sector' =>
                                    $holding
                                        ->security
                                        ?->sector,

                                'quantity' =>
                                    $quantity,

                                'price' =>
                                    $price !== null
                                        ? round(
                                            $price,
                                            4,
                                        )
                                        : null,

                                'market_value' =>
                                    round(
                                        $marketValue,
                                        2,
                                    ),

                                'weight' =>
                                    $value > 0
                                        ? round(
                                            (
                                                $marketValue
                                                / $value
                                            ) * 100,
                                            2,
                                        )
                                        : 0,

                                'cost_basis' =>
                                    $holding
                                            ->cost_basis
                                        !== null
                                            ? round(
                                                (float)
                                                $holding
                                                    ->cost_basis,
                                                2,
                                            )
                                            : null,

                                'unrealized_gain_loss' =>
                                    $holding
                                            ->unrealized_gain_loss
                                        !== null
                                            ? round(
                                                (float)
                                                $holding
                                                    ->unrealized_gain_loss,
                                                2,
                                            )
                                            : null,

                                'as_of_date' =>
                                    $holding
                                        ->as_of_date
                                        ?->toDateString(),
                            ];
                        },
                    )
                    ->values()
                    ->all();
        }

        return $data;
    }

    private function accountValue(
        InvestmentAccount $account,
    ): float {
        /*
         * Prefer the provider/account-level value
         * when it exists.
         */
        if (
            $account->current_value !== null
            && (float)
                $account->current_value > 0
        ) {
            return (float)
                $account->current_value;
        }

        /*
         * Otherwise calculate the display value
         * from holdings + cash.
         */
        $holdingsValue =
            (float) $account
                ->holdings
                ->sum(
                    fn ($holding): float =>
                        max(
                            0,
                            (float) (
                                $holding->market_value
                                ?? 0
                            ),
                        ),
                );

        return $holdingsValue
            + max(
                0,
                (float) (
                    $account->cash_value
                    ?? 0
                ),
            );
    }
}