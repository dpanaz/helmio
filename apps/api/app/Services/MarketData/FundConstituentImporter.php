<?php

namespace App\Services\MarketData;

use App\Models\FundConstituent;
use App\Models\Security;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FundConstituentImporter
{
    public function __construct(
        private readonly AlphaVantageFundDataService $fundData,
    ) {
    }

    /**
     * Import the latest known look-through holdings for an ETF.
     *
     * Alpha Vantage ETF_PROFILE provides top holdings rather than
     * necessarily the entire constituent universe, so Helmio stores
     * these rows as known look-through exposure and separately reports
     * the total imported weight coverage.
     *
     * @return array<string, mixed>
     */
    public function import(
        Security $fund,
    ): array {
        $symbol = strtoupper(
            trim(
                (string) $fund->symbol,
            ),
        );

        $securityType = strtolower(
            trim(
                (string) $fund->security_type,
            ),
        );

        if ($symbol === '') {
            return [
                'fund_security_id' =>
                    $fund->id,

                'symbol' =>
                    null,

                'status' =>
                    'skipped',

                'imported' =>
                    0,

                'coverage' =>
                    0.0,

                'reason' =>
                    'Fund security has no symbol.',
            ];
        }

        if ($securityType !== 'etf') {
            return [
                'fund_security_id' =>
                    $fund->id,

                'symbol' =>
                    $symbol,

                'status' =>
                    'skipped',

                'imported' =>
                    0,

                'coverage' =>
                    0.0,

                'reason' =>
                    sprintf(
                        'Alpha Vantage look-through currently supports ETFs only; %s is "%s".',
                        $symbol,
                        $securityType !== ''
                            ? $securityType
                            : 'unknown',
                    ),
            ];
        }

        $profile =
            $this->fundData
                ->etfProfile(
                    $symbol,
                );

        $holdings =
            collect(
                $profile['holdings']
                ?? [],
            );

        if ($holdings->isEmpty()) {
            return [
                'fund_security_id' =>
                    $fund->id,

                'symbol' =>
                    $symbol,

                'status' =>
                    'insufficient_data',

                'imported' =>
                    0,

                'coverage' =>
                    0.0,

                'reason' =>
                    'No ETF holdings were returned by Alpha Vantage.',
            ];
        }

        /*
         * Alpha Vantage does not need to be queried every time
         * diversification is rendered. We persist one "current"
         * snapshot dated on import day and replace only that day's
         * rows when rerun.
         */
        $asOfDate =
            now()->toDateString();

        $imported = 0;

        $skipped = 0;

        $coverage = 0.0;

        DB::transaction(
            function () use (
                $fund,
                $symbol,
                $holdings,
                $asOfDate,
                &$imported,
                &$skipped,
                &$coverage,
            ): void {
                /*
                 * Make rerunning the same day's import idempotent.
                 */
                FundConstituent::query()
                    ->where(
                        'fund_security_id',
                        $fund->id,
                    )
                    ->whereDate(
                        'as_of_date',
                        $asOfDate,
                    )
                    ->delete();

                foreach (
                    $holdings
                    as $holding
                ) {
                    $constituentSymbol =
                        strtoupper(
                            trim(
                                (string) (
                                    $holding['symbol']
                                    ?? ''
                                )
                            ),
                        );

                    /*
                     * Some provider rows use placeholders such as N/A
                     * instead of a real tradable symbol. Those rows must
                     * not be resolved into a shared Security record or they
                     * will collide with the unique constituent constraint.
                     */
                    if (
                        $this->isPlaceholderSymbol(
                            $constituentSymbol,
                        )
                    ) {
                        $skipped++;

                        continue;
                    }

                    $weight =
                        isset(
                            $holding['weight']
                        )
                        && is_numeric(
                            $holding['weight']
                        )
                            ? (float) $holding[
                                'weight'
                            ]
                            : null;

                    if (
                        $constituentSymbol === ''
                        || $weight === null
                        || $weight <= 0
                    ) {
                        $skipped++;

                        continue;
                    }

                    /*
                     * Avoid pathological self-reference if provider
                     * data is ever malformed.
                     */
                    if (
                        $constituentSymbol
                        === $symbol
                    ) {
                        $skipped++;

                        continue;
                    }

                    $constituent =
                        $this
                            ->resolveConstituentSecurity(
                                symbol:
                                    $constituentSymbol,

                                name:
                                    $holding['name']
                                    ?? null,

                                metadata:
                                    $holding['metadata']
                                    ?? [],
                            );

                    FundConstituent::query()
                        ->updateOrCreate(
                            [
                                'fund_security_id' =>
                                    $fund->id,

                                'constituent_security_id' =>
                                    $constituent->id,

                                'as_of_date' =>
                                    $asOfDate,
                            ],
                            [
                                'weight' =>
                                    $weight,

                                'source' =>
                                    'alpha_vantage',

                                'metadata' => [
                                    'fund_symbol' =>
                                        $symbol,

                                    'constituent_symbol' =>
                                        $constituentSymbol,

                                    'provider' =>
                                        'alpha_vantage',

                                    'coverage_type' =>
                                        'top_holdings',

                                    'provider_metadata' =>
                                        $holding[
                                            'metadata'
                                        ] ?? [],
                                ],
                            ],
                        );

                    $coverage +=
                        $weight;

                    $imported++;
                }

                /*
                 * Store look-through metadata on the parent ETF.
                 *
                 * Do not replace unrelated Security metadata.
                 */
                $metadata =
                    is_array(
                        $fund->metadata,
                    )
                        ? $fund->metadata
                        : [];

                data_set(
                    $metadata,
                    'look_through.provider',
                    'alpha_vantage',
                );

                data_set(
                    $metadata,
                    'look_through.as_of_date',
                    $asOfDate,
                );

                data_set(
                    $metadata,
                    'look_through.imported_constituent_count',
                    $imported,
                );

                data_set(
                    $metadata,
                    'look_through.weight_coverage',
                    round(
                        $coverage,
                        10,
                    ),
                );

                data_set(
                    $metadata,
                    'look_through.coverage_type',
                    'top_holdings',
                );

                $fund->forceFill([
                    'metadata' =>
                        $metadata,
                ])->save();
            },
        );

        return [
            'fund_security_id' =>
                $fund->id,

            'symbol' =>
                $symbol,

            'status' =>
                $imported > 0
                    ? 'complete'
                    : 'insufficient_data',

            'imported' =>
                $imported,

            'skipped' =>
                $skipped,

            'coverage' =>
                round(
                    $coverage,
                    10,
                ),

            'coverage_percent' =>
                round(
                    $coverage * 100,
                    2,
                ),

            'as_of_date' =>
                $asOfDate,

            'source' =>
                'alpha_vantage',

            'coverage_type' =>
                'top_holdings',
        ];
    }

    private function isPlaceholderSymbol(
        string $symbol,
    ): bool {
        $symbol = strtoupper(
            trim($symbol),
        );

        return in_array(
            $symbol,
            [
                '',
                'N/A',
                'NA',
                'N.A.',
                'NONE',
                'NULL',
                'UNKNOWN',
                '-',
                '--',
            ],
            true,
        );
    }

    private function resolveConstituentSecurity(
        string $symbol,
        ?string $name,
        array $metadata = [],
    ): Security {
        $security =
            Security::query()
                ->where(
                    'symbol',
                    $symbol,
                )
                ->first();

        if ($security !== null) {
            /*
             * Preserve existing Helmio/provider classification data.
             * Only fill a missing name when the provider supplied one.
             */
            if (
                blank(
                    $security->name,
                )
                && filled(
                    $name,
                )
            ) {
                $security->forceFill([
                    'name' =>
                        trim(
                            (string) $name,
                        ),
                ])->save();
            }

            return $security;
        }

        return Security::query()
            ->create([
                'symbol' =>
                    $symbol,

                'name' =>
                    filled(
                        $name,
                    )
                        ? trim(
                            (string) $name,
                        )
                        : $symbol,

                'security_type' =>
                    'stock',

                'currency' =>
                    'USD',

                'metadata' => [
                    'created_from' =>
                        'fund_constituent_import',

                    'provider' =>
                        'alpha_vantage',

                    'provider_metadata' =>
                        $metadata,
                ],
            ]);
    }
}