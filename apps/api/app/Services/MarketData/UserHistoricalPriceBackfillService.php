<?php

namespace App\Services\MarketData;

use App\Models\Security;
use App\Models\User;
use Carbon\CarbonInterface;
use Throwable;

class UserHistoricalPriceBackfillService
{
    public function __construct(
        private readonly HistoricalSecurityPriceImporter $importer,
    ) {
    }

    public function backfill(
        User $user,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
    ): array {
        $securities = Security::query()
            ->whereHas(
                'holdings.investmentAccount',
                fn ($query) =>
                    $query->where(
                        'user_id',
                        $user->id,
                    ),
            )
            ->get();

        $results = [];

        foreach ($securities as $security) {
            $symbol = trim(
                (string) (
                    $security->symbol
                    ?? ''
                ),
            );

            /*
             * Cash / cash-equivalent securities do not need historical
             * market prices for investment-performance calculations.
             */
            if (
                strtolower(
                    (string) $security->security_type,
                ) === 'cash'
            ) {
                $results[] = [
                    'security_id' =>
                        $security->id,

                    'symbol' =>
                        $symbol !== ''
                            ? $symbol
                            : 'unknown',

                    'status' =>
                        'skipped_cash',

                    'imported' =>
                        0,

                    'message' =>
                        'Cash security does not require historical prices.',
                ];

                continue;
            }

            /*
             * A market-data request without a symbol or FIGI can never
             * succeed. Skip it instead of aborting the entire user backfill.
             */
            if (
                $symbol === ''
                && blank(
                    $security->figi
                    ?? null,
                )
            ) {
                $results[] = [
                    'security_id' =>
                        $security->id,

                    'symbol' =>
                        'unknown',

                    'status' =>
                        'skipped_missing_identifier',

                    'imported' =>
                        0,

                    'message' =>
                        'Security has no usable symbol or FIGI.',
                ];

                continue;
            }

            try {
                $result =
                    $this->importer->import(
                        security:
                            $security,

                        startDate:
                            $startDate,

                        endDate:
                            $endDate,
                    );

                /*
                 * Normalize the result shape so the console command can
                 * safely print every row.
                 */
                $results[] = array_merge(
                    [
                        'security_id' =>
                            $security->id,

                        'symbol' =>
                            $symbol !== ''
                                ? $symbol
                                : (
                                    $security->figi
                                    ?? 'unknown'
                                ),

                        'status' =>
                            'complete',

                        'imported' =>
                            0,
                    ],
                    is_array($result)
                        ? $result
                        : [],
                );
            } catch (Throwable $exception) {
                report($exception);

                $message =
                    $exception->getMessage();

                $normalizedMessage =
                    strtolower($message);

                $invalidIdentifier =
                    str_contains(
                        $normalizedMessage,
                        '404',
                    )
                    || str_contains(
                        $normalizedMessage,
                        'symbol',
                    )
                    && (
                        str_contains(
                            $normalizedMessage,
                            'missing',
                        )
                        || str_contains(
                            $normalizedMessage,
                            'invalid',
                        )
                    );

                $results[] = [
                    'security_id' =>
                        $security->id,

                    'symbol' =>
                        $symbol !== ''
                            ? $symbol
                            : (
                                $security->figi
                                ?? 'unknown'
                            ),

                    'status' =>
                        $invalidIdentifier
                            ? 'skipped_invalid_symbol'
                            : 'failed',

                    'imported' =>
                        0,

                    'message' =>
                        $invalidIdentifier
                            ? 'Market-data provider did not recognize this security identifier.'
                            : $message,
                ];

                /*
                 * Most important behavior:
                 * continue processing the remaining securities.
                 */
                continue;
            }
        }

        return [
            'user_id' =>
                $user->id,

            'security_count' =>
                $securities->count(),

            'successful_count' =>
                collect($results)
                    ->whereIn(
                        'status',
                        [
                            'complete',
                            'success',
                            'imported',
                        ],
                    )
                    ->count(),

            'skipped_count' =>
                collect($results)
                    ->filter(
                        fn (array $result): bool =>
                            str_starts_with(
                                (string) (
                                    $result['status']
                                    ?? ''
                                ),
                                'skipped_',
                            ),
                    )
                    ->count(),

            'failed_count' =>
                collect($results)
                    ->where(
                        'status',
                        'failed',
                    )
                    ->count(),

            'results' =>
                $results,
        ];
    }
}