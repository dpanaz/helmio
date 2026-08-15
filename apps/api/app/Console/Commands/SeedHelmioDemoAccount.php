<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Production-safe Helmio demo account population command.
 *
 * Usage:
 *   php artisan helmio:seed-demo --email=demo@example.com --force
 *
 * Safety:
 * - Requires an EXISTING user email.
 * - Only creates/updates an account named "Helmio Advisor Demo".
 * - Uses DEMO-* provider transaction IDs so reruns are idempotent.
 * - Does not delete or modify any other account.
 */
class SeedHelmioDemoAccount extends Command
{
    protected $signature = 'helmio:seed-demo
        {--email= : Existing Helmio demo user email}
        {--force : Required outside local/testing environments}';

    protected $description = 'Populate a designated Helmio demo user with a rich ~$500,000 advisor-managed portfolio';

    private array $columnCache = [];

    public function handle(): int
    {
        $email = trim((string) $this->option('email'));

        if ($email === '') {
            $this->error('Pass the EXISTING demo user email with --email=...');
            return self::FAILURE;
        }

        if (! app()->environment(['local', 'testing']) && ! $this->option('force')) {
            $this->error('Refusing to seed a non-local environment without --force.');
            return self::FAILURE;
        }

        if (! Schema::hasTable('users')) {
            $this->error('users table not found.');
            return self::FAILURE;
        }

        $user = DB::table('users')->where('email', $email)->first();

        if (! $user) {
            $this->error("No existing Helmio user found for {$email}.");
            $this->line('Create/login the demo user through Helmio first, then rerun this command.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($user): void {
            $accountId = $this->seedAccount((int) $user->id);
            $securities = $this->seedSecurities();
            $this->seedHoldings($accountId, $securities);
            $this->seedSyntheticSecurityPrices($securities);
            $this->seedTransactions($accountId, $securities);
            $this->seedValuations((int) $user->id, $accountId);
            $this->seedBenchmarkHistory();
        });

        $this->newLine();
        $this->info('Helmio demo account populated successfully.');
        $this->table(
            ['Demo metric', 'Value'],
            [
                ['Total portfolio value', '$500,000'],
                ['Invested assets', '$451,350'],
                ['Cash', '$48,650'],
                ['Advisor fee', '1.25% annual'],
                ['High-cost funds', '3'],
                ['Concentrated technology exposure', 'Yes'],
                ['Round-trip / short-term trades', 'Multiple'],
                ['Trading fees', 'Multiple'],
                ['Realized short-term gains/losses', 'Yes'],
                ['Deposits & withdrawals', 'Yes'],
                ['Historical valuations', '13 months'],
                ['Benchmark history', '13 months'],
            ]
        );

        $this->newLine();
        $this->line('Next: run Helmio’s normal analytics / audit pipeline for this user, then open the dashboard.');
        $this->line('The dataset is intentionally unhealthy so the six analytics categories produce visible findings.');

        return self::SUCCESS;
    }

    private function seedAccount(int $userId): int
    {
        $table = 'investment_accounts';

        if (! Schema::hasTable($table)) {
            throw new RuntimeException("{$table} table not found.");
        }

        $nameColumn = $this->firstColumn($table, ['name', 'account_name', 'nickname']);
        if (! $nameColumn) {
            throw new RuntimeException("Could not find an account-name column on {$table}.");
        }

        $existing = DB::table($table)
            ->where('user_id', $userId)
            ->where($nameColumn, 'Helmio Advisor Demo')
            ->first();

        $payload = [
            'user_id' => $userId,
            $nameColumn => 'Helmio Advisor Demo',
            'institution' => 'Summit Private Wealth — Demo',
            'institution_name' => 'Summit Private Wealth — Demo',
            'provider' => 'demo',
            'provider_name' => 'Helmio Demo Provider',
            'account_type' => 'taxable_brokerage',
            'type' => 'brokerage',
            'tax_status' => 'taxable',
            'currency' => 'USD',
            'cash_balance' => 48650.00,
            'cash_value' => 48650.00,
            'current_value' => 500000.00,
            'market_value' => 500000.00,
            'balance' => 500000.00,
            'advisory_fee_rate' => 0.0125,
            'annual_advisory_fee_rate' => 0.0125,
            'management_fee_rate' => 0.0125,
            'is_manual' => true,
            'is_active' => true,
            'metadata' => json_encode([
                'demo' => true,
                'advisor_name' => 'Summit Private Wealth — Demo',
                'purpose' => 'Helmio production demonstration account',
            ]),
            'updated_at' => now(),
            'created_at' => now(),
        ];

        if ($existing) {
            $this->updateFlexible($table, ['id' => $existing->id], $payload);
            return (int) $existing->id;
        }

        $id = $this->insertGetIdFlexible($table, $payload);

        $this->line("Created demo investment account #{$id}.");

        return $id;
    }

    private function seedSecurities(): array
    {
        $table = 'securities';

        if (! Schema::hasTable($table)) {
            throw new RuntimeException("{$table} table not found.");
        }

        // Fictional DEM* funds let the demo intentionally show high expenses
        // without representing a real security inaccurately.
        $definitions = [
            'AAPL' => [
                'name' => 'Apple Inc.',
                'security_type' => 'stock',
                'asset_class' => 'us_equity',
                'sector' => 'Technology',
                'expense_ratio' => 0.0000,
            ],
            'NVDA' => [
                'name' => 'NVIDIA Corporation',
                'security_type' => 'stock',
                'asset_class' => 'us_equity',
                'sector' => 'Technology',
                'expense_ratio' => 0.0000,
            ],
            'SPY' => [
                'name' => 'SPDR S&P 500 ETF Trust',
                'security_type' => 'etf',
                'asset_class' => 'us_equity',
                'sector' => 'Broad Market',
                'expense_ratio' => 0.0009,
            ],
            'QQQ' => [
                'name' => 'Invesco QQQ Trust',
                'security_type' => 'etf',
                'asset_class' => 'us_equity',
                'sector' => 'Technology',
                'expense_ratio' => 0.0020,
            ],
            'DEMGX' => [
                'name' => 'Demo Strategic Growth Fund A',
                'security_type' => 'mutual_fund',
                'asset_class' => 'us_equity',
                'sector' => 'Large Growth',
                'expense_ratio' => 0.0148,
            ],
            'DEMTX' => [
                'name' => 'Demo Tactical Allocation Fund A',
                'security_type' => 'mutual_fund',
                'asset_class' => 'allocation',
                'sector' => 'Multi-Asset',
                'expense_ratio' => 0.0122,
            ],
            'DEMIX' => [
                'name' => 'Demo Income Opportunities Fund A',
                'security_type' => 'mutual_fund',
                'asset_class' => 'fixed_income',
                'sector' => 'Income',
                'expense_ratio' => 0.0095,
            ],
            'RTRA' => [
                'name' => 'Demo Round Trip Alpha',
                'security_type' => 'stock',
                'asset_class' => 'us_equity',
                'sector' => 'Industrials',
                'expense_ratio' => 0.0000,
            ],
            'RTRB' => [
                'name' => 'Demo Round Trip Beta',
                'security_type' => 'stock',
                'asset_class' => 'us_equity',
                'sector' => 'Consumer Discretionary',
                'expense_ratio' => 0.0000,
            ],
            'RTRC' => [
                'name' => 'Demo Round Trip Gamma',
                'security_type' => 'stock',
                'asset_class' => 'us_equity',
                'sector' => 'Healthcare',
                'expense_ratio' => 0.0000,
            ],
        ];

        $ids = [];

        foreach ($definitions as $symbol => $definition) {
            $existing = DB::table($table)->where('symbol', $symbol)->first();

            $payload = array_merge($definition, [
                'symbol' => $symbol,
                'ticker' => $symbol,
                'currency' => 'USD',
                'is_active' => true,
                'metadata' => json_encode([
                    'demo' => Str::startsWith($symbol, ['DEM', 'RTR']),
                ]),
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            if ($existing) {
                // Do NOT overwrite real security metadata for real tickers.
                if (Str::startsWith($symbol, ['DEM', 'RTR'])) {
                    $this->updateFlexible($table, ['id' => $existing->id], $payload);
                }

                $ids[$symbol] = (int) $existing->id;
                continue;
            }

            $ids[$symbol] = $this->insertGetIdFlexible($table, $payload);
        }

        return $ids;
    }

    private function seedHoldings(int $accountId, array $securityIds): void
    {
        $table = $this->firstExistingTable(['investment_holdings', 'holdings']);

        if (! $table) {
            $this->warn('No holdings table found; skipping current holdings.');
            return;
        }

        $holdings = [
            // symbol => [quantity, price, market value, cost basis]
            'AAPL'  => [250, 200.00, 50000.00, 42000.00],
            'NVDA'  => [300, 180.00, 54000.00, 39000.00],
            'DEMGX' => [1200, 58.00, 69600.00, 72000.00],
            'DEMTX' => [1000, 74.00, 74000.00, 76000.00],
            'DEMIX' => [2000, 25.00, 50000.00, 52000.00],
            'SPY'   => [150, 645.00, 96750.00, 81000.00],
            'QQQ'   => [100, 570.00, 57000.00, 49000.00],
        ];

        foreach ($holdings as $symbol => [$qty, $price, $value, $costBasis]) {
            $match = [
                'investment_account_id' => $accountId,
                'security_id' => $securityIds[$symbol],
                'as_of_date' => now()->toDateString(),
            ];

            $payload = [
                ...$match,

                'quantity' => $qty,
                'shares' => $qty,

                'price' => $price,
                'current_price' => $price,

                'market_value' => $value,
                'current_value' => $value,

                'cost_basis' => $costBasis,
                'average_cost' => $costBasis / $qty,

                'currency' => 'USD',

                'metadata' => json_encode([
                    'demo' => true,
                ]),

                'updated_at' => now(),
                'created_at' => now(),
            ];

            $this->upsertFlexible($table, $match, $payload);
        }

        $this->line('Seeded current holdings: $451,350 invested + $48,650 cash.');
    }

    private function seedSyntheticSecurityPrices(array $securityIds): void
    {
        $table = $this->firstExistingTable([
            'security_prices',
            'historical_security_prices',
        ]);

        if (! $table) {
            $this->warn('No security price table found; skipping synthetic demo price history.');
            return;
        }

        // Deterministic synthetic monthly prices for the entire demo portfolio.
        // These are NOT intended to represent actual historical prices for real securities.
        // Their sole purpose is to make the demo account self-contained and reproducible.
        $series = [
            'AAPL' => [
                ['2025-08-01', 165.00], ['2025-08-31', 168.00], ['2025-09-30', 172.00],
                ['2025-10-31', 169.00], ['2025-11-30', 176.00], ['2025-12-31', 181.00],
                ['2026-01-31', 177.00], ['2026-02-28', 184.00], ['2026-03-31', 179.00],
                ['2026-04-30', 193.00], ['2026-05-31', 188.00], ['2026-06-30', 196.00],
                ['2026-07-31', 198.00], ['2026-08-13', 200.00],
            ],
            'NVDA' => [
                ['2025-08-01', 128.00], ['2025-08-31', 130.00], ['2025-09-30', 134.00],
                ['2025-10-31', 129.00], ['2025-11-30', 142.00], ['2025-12-31', 148.00],
                ['2026-01-31', 145.00], ['2026-02-28', 151.00], ['2026-03-31', 143.00],
                ['2026-04-30', 158.00], ['2026-05-31', 154.00], ['2026-06-30', 166.00],
                ['2026-07-31', 174.00], ['2026-08-13', 180.00],
            ],
            'SPY' => [
                ['2025-08-01', 538.00], ['2025-08-31', 540.00], ['2025-09-30', 552.00],
                ['2025-10-31', 560.00], ['2025-11-30', 574.00], ['2025-12-31', 586.00],
                ['2026-01-31', 595.00], ['2026-02-28', 590.00], ['2026-03-31', 602.00],
                ['2026-04-30', 616.00], ['2026-05-31', 622.00], ['2026-06-30', 634.00],
                ['2026-07-31', 640.00], ['2026-08-13', 645.00],
            ],
            'QQQ' => [
                ['2025-08-01', 486.00], ['2025-08-31', 490.00], ['2025-09-30', 501.00],
                ['2025-10-31', 495.00], ['2025-11-30', 514.00], ['2025-12-31', 523.00],
                ['2026-01-31', 515.00], ['2026-02-28', 525.00], ['2026-03-31', 510.00],
                ['2026-04-30', 536.00], ['2026-05-31', 529.00], ['2026-06-30', 548.00],
                ['2026-07-31', 559.00], ['2026-08-13', 570.00],
            ],
            'DEMGX' => [
                ['2025-08-01', 60.00], ['2025-08-31', 59.50], ['2025-09-30', 60.20],
                ['2025-10-31', 57.80], ['2025-11-30', 59.10], ['2025-12-31', 58.70],
                ['2026-01-31', 56.90], ['2026-02-28', 58.40], ['2026-03-31', 55.50],
                ['2026-04-30', 57.20], ['2026-05-31', 56.60], ['2026-06-30', 57.50],
                ['2026-07-31', 57.70], ['2026-08-13', 58.00],
            ],
            'DEMTX' => [
                ['2025-08-01', 76.00], ['2025-08-31', 75.40], ['2025-09-30', 76.10],
                ['2025-10-31', 73.80], ['2025-11-30', 75.20], ['2025-12-31', 74.60],
                ['2026-01-31', 72.90], ['2026-02-28', 74.30], ['2026-03-31', 71.80],
                ['2026-04-30', 73.10], ['2026-05-31', 72.70], ['2026-06-30', 73.60],
                ['2026-07-31', 73.80], ['2026-08-13', 74.00],
            ],
            'DEMIX' => [
                ['2025-08-01', 26.00], ['2025-08-31', 25.90], ['2025-09-30', 25.80],
                ['2025-10-31', 25.60], ['2025-11-30', 25.70], ['2025-12-31', 25.50],
                ['2026-01-31', 25.40], ['2026-02-28', 25.30], ['2026-03-31', 25.10],
                ['2026-04-30', 25.20], ['2026-05-31', 25.10], ['2026-06-30', 25.00],
                ['2026-07-31', 25.00], ['2026-08-13', 25.00],
            ],
            'RTRA' => [
                ['2025-08-01', 40.00], ['2025-08-31', 42.00], ['2025-09-30', 44.40],
                ['2025-10-31', 43.00], ['2025-11-30', 43.20], ['2025-12-31', 44.10],
                ['2026-01-31', 42.30], ['2026-02-28', 44.00], ['2026-03-31', 43.60],
                ['2026-04-30', 45.10], ['2026-05-31', 44.30], ['2026-06-30', 45.00],
                ['2026-07-31', 44.80], ['2026-08-13', 45.20],
            ],
            'RTRB' => [
                ['2025-08-01', 30.00], ['2025-08-31', 31.00], ['2025-09-30', 30.80],
                ['2025-10-31', 29.10], ['2025-11-30', 30.20], ['2025-12-31', 32.70],
                ['2026-01-31', 32.00], ['2026-02-28', 33.00], ['2026-03-31', 31.75],
                ['2026-04-30', 31.20], ['2026-05-31', 29.80], ['2026-06-30', 32.10],
                ['2026-07-31', 31.70], ['2026-08-13', 31.90],
            ],
            'RTRC' => [
                ['2025-08-01', 54.00], ['2025-08-31', 55.00], ['2025-09-30', 55.80],
                ['2025-10-31', 57.30], ['2025-11-30', 56.20], ['2025-12-31', 58.00],
                ['2026-01-31', 54.40], ['2026-02-28', 55.10], ['2026-03-31', 53.20],
                ['2026-04-30', 55.10], ['2026-05-31', 54.60], ['2026-06-30', 57.00],
                ['2026-07-31', 54.25], ['2026-08-13', 55.00],
            ],
        ];

        $dateColumn = $this->firstColumn($table, ['price_date', 'date', 'as_of_date']);
        $closeColumn = $this->firstColumn($table, ['close_price', 'close', 'price']);
        $adjustedColumn = $this->firstColumn($table, ['adjusted_close_price', 'adjusted_close']);

        if (! $dateColumn || ! $closeColumn) {
            $this->warn("Could not identify date/price columns on {$table}; skipping synthetic price history.");
            return;
        }

        $count = 0;

        foreach ($series as $symbol => $points) {
            if (! isset($securityIds[$symbol])) {
                continue;
            }

            foreach ($points as [$date, $price]) {
                $match = [
                    'security_id' => $securityIds[$symbol],
                    $dateColumn => $date,
                ];

                $payload = [
                    ...$match,
                    $closeColumn => $price,
                    'source' => 'demo',
                    'metadata' => json_encode([
                        'demo' => true,
                        'synthetic' => true,
                    ]),
                    'updated_at' => now(),
                    'created_at' => now(),
                ];

                if ($adjustedColumn) {
                    $payload[$adjustedColumn] = $price;
                }

                $this->upsertFlexible($table, $match, $payload);
                $count++;
            }
        }

        $this->line("Seeded {$count} synthetic demo security prices.");
    }

    private function seedTransactions(int $accountId, array $securityIds): void
    {
        $table = 'investment_transactions';

        if (! Schema::hasTable($table)) {
            $this->warn("{$table} table not found; skipping transactions.");
            return;
        }

        $rows = [];

        // External cash flows.
        $rows[] = $this->tx($accountId, null, 'deposit', '2025-08-15', 0, 0, 250000, 0, 250000, 'Initial account funding');
        $rows[] = $this->tx($accountId, null, 'deposit', '2025-09-03', 0, 0, 225000, 0, 225000, 'Additional transfer from savings');
        $rows[] = $this->tx($accountId, null, 'withdrawal', '2026-03-18', 0, 0, -18000, 0, -18000, 'Client cash withdrawal');
        $rows[] = $this->tx($accountId, null, 'deposit', '2026-06-12', 0, 0, 30000, 0, 30000, 'Additional contribution');

        // Core purchases / portfolio construction.
        $rows[] = $this->tx($accountId, $securityIds['AAPL'], 'buy', '2025-08-18', 250, 168, -42000, 24.95, -42024.95, 'Purchase Apple');
        $rows[] = $this->tx($accountId, $securityIds['NVDA'], 'buy', '2025-08-18', 300, 130, -39000, 24.95, -39024.95, 'Purchase NVIDIA');
        $rows[] = $this->tx($accountId, $securityIds['DEMGX'], 'buy', '2025-08-20', 1200, 60, -72000, 75, -72075, 'Purchase high-cost growth fund');
        $rows[] = $this->tx($accountId, $securityIds['DEMTX'], 'buy', '2025-08-20', 1000, 76, -76000, 75, -76075, 'Purchase high-cost tactical fund');
        $rows[] = $this->tx($accountId, $securityIds['DEMIX'], 'buy', '2025-08-21', 2000, 26, -52000, 75, -52075, 'Purchase high-cost income fund');
        $rows[] = $this->tx($accountId, $securityIds['SPY'], 'buy', '2025-09-05', 150, 540, -81000, 4.95, -81004.95, 'Purchase broad-market ETF');
        $rows[] = $this->tx($accountId, $securityIds['QQQ'], 'buy', '2025-09-05', 100, 490, -49000, 4.95, -49004.95, 'Purchase growth ETF');

        // Advisory fees: roughly 1.25% annualized, charged quarterly.
        foreach ([
            ['2025-09-30', -1450.00],
            ['2025-12-31', -1515.00],
            ['2026-03-31', -1490.00],
            ['2026-06-30', -1575.00],
        ] as [$date, $amount]) {
            $rows[] = $this->tx($accountId, null, 'advisory_fee', $date, 0, 0, $amount, 0, $amount, 'Quarterly advisory management fee');
        }

        // Dividends / income.
        foreach ([
            ['2025-10-15', 'DEMIX', 510.00],
            ['2025-12-18', 'SPY', 285.00],
            ['2026-01-15', 'DEMIX', 525.00],
            ['2026-03-20', 'SPY', 300.00],
            ['2026-04-15', 'DEMIX', 515.00],
            ['2026-06-19', 'SPY', 310.00],
            ['2026-07-15', 'DEMIX', 530.00],
        ] as [$date, $symbol, $amount]) {
            $rows[] = $this->tx($accountId, $securityIds[$symbol], 'dividend', $date, 0, 0, $amount, 0, $amount, "{$symbol} distribution", [
                'is_qualified_dividend' => $symbol === 'SPY',
            ]);
        }

        // Repeated short-duration round trips to make Trading Discipline light up.
        $roundTrips = [
            ['RTRA', '2025-09-10', '2025-09-24', 500, 42.00, 44.40, 29.95, 38.95],
            ['RTRB', '2025-10-02', '2025-10-17', 800, 31.00, 29.10, 34.95, 41.95],
            ['RTRC', '2025-10-21', '2025-11-03', 600, 55.00, 57.30, 29.95, 36.95],
            ['RTRA', '2025-11-12', '2025-11-28', 700, 45.10, 43.20, 34.95, 41.95],
            ['RTRB', '2025-12-04', '2025-12-19', 900, 30.20, 32.70, 39.95, 46.95],
            ['RTRC', '2026-01-06', '2026-01-20', 500, 58.00, 54.40, 29.95, 36.95],
            ['RTRA', '2026-02-03', '2026-02-18', 1000, 41.70, 44.00, 44.95, 51.95],
            ['RTRB', '2026-03-02', '2026-03-13', 1100, 33.00, 31.75, 49.95, 56.95],
            ['RTRC', '2026-04-07', '2026-04-24', 750, 52.00, 55.10, 34.95, 41.95],
            ['RTRA', '2026-05-05', '2026-05-20', 1200, 46.00, 44.30, 54.95, 61.95],
            ['RTRB', '2026-06-03', '2026-06-22', 950, 29.80, 32.10, 44.95, 51.95],
            ['RTRC', '2026-07-06', '2026-07-24', 850, 57.00, 54.25, 39.95, 46.95],
        ];

        foreach ($roundTrips as $i => [$symbol, $buyDate, $sellDate, $qty, $buyPrice, $sellPrice, $buyFee, $sellFee]) {
            $buyGross = -($qty * $buyPrice);
            $sellGross = $qty * $sellPrice;
            $gainLoss = ($sellPrice - $buyPrice) * $qty - $buyFee - $sellFee;

            $rows[] = $this->tx(
                $accountId,
                $securityIds[$symbol],
                'buy',
                $buyDate,
                $qty,
                $buyPrice,
                $buyGross,
                $buyFee,
                $buyGross - $buyFee,
                "Short-term tactical purchase {$symbol}"
            );

            $rows[] = $this->tx(
                $accountId,
                $securityIds[$symbol],
                'sell',
                $sellDate,
                $qty,
                $sellPrice,
                $sellGross,
                $sellFee,
                $sellGross - $sellFee,
                "Short-term tactical sale {$symbol}",
                [
                    'realized_gain_loss' => $gainLoss,
                    'holding_period_days' => max(1, (int) \Carbon\Carbon::parse($buyDate)->diffInDays(\Carbon\Carbon::parse($sellDate))),
                ]
            );
        }

        // Additional trading activity around existing holdings.
        $rows[] = $this->tx($accountId, $securityIds['QQQ'], 'sell', '2026-01-28', 40, 515, 20600, 9.95, 20590.05, 'Trim QQQ position', [
            'realized_gain_loss' => 990.05,
            'holding_period_days' => 145,
        ]);
        $rows[] = $this->tx($accountId, $securityIds['QQQ'], 'buy', '2026-02-09', 40, 525, -21000, 9.95, -21009.95, 'Re-enter QQQ after short interval');
        $rows[] = $this->tx($accountId, $securityIds['AAPL'], 'sell', '2026-04-01', 100, 190, 19000, 12.95, 18987.05, 'Trim Apple position', [
            'realized_gain_loss' => 2187.05,
            'holding_period_days' => 226,
        ]);
        $rows[] = $this->tx($accountId, $securityIds['AAPL'], 'buy', '2026-04-20', 100, 196, -19600, 12.95, -19612.95, 'Repurchase Apple');

        foreach ($rows as $index => $row) {
            $providerId = sprintf('DEMO-%04d-%s', $index + 1, substr(sha1(json_encode($row)), 0, 10));

            $row['provider_transaction_id'] = $providerId;
            $row['metadata'] = json_encode(array_merge(
                ['demo' => true],
                json_decode($row['metadata'] ?? '{}', true) ?: []
            ));
            $row['updated_at'] = now();
            $row['created_at'] = now();

            $this->upsertFlexible(
                $table,
                ['provider_transaction_id' => $providerId],
                $row
            );
        }

        $this->line('Seeded ' . count($rows) . ' demo transactions.');
    }

    private function seedValuations(int $userId, int $accountId): void
    {
        $table = 'portfolio_valuations';

        if (! Schema::hasTable($table)) {
            $this->warn("{$table} table not found; skipping historical valuations.");
            return;
        }

        // Intentionally choppy and ultimately weaker than benchmark growth.
        $values = [
            ['2025-08-31', 474000,   0],
            ['2025-09-30', 478500,   0],
            ['2025-10-31', 469000,   0],
            ['2025-11-30', 481500,   0],
            ['2025-12-31', 486000,   0],
            ['2026-01-31', 477000,   0],
            ['2026-02-28', 489500,   0],
            ['2026-03-31', 463000, -18000],
            ['2026-04-30', 475000,   0],
            ['2026-05-31', 468500,   0],
            ['2026-06-30', 502500, 30000],
            ['2026-07-31', 491500,   0],
            ['2026-08-13', 500000,   0],
        ];

        foreach ($values as [$date, $totalValue, $cashFlow]) {
            $cash = $date === '2026-08-13' ? 48650.00 : round($totalValue * 0.07, 2);
            $market = $totalValue - $cash;

            $match = [
                'user_id' => $userId,
                'investment_account_id' => $accountId,
                'valuation_date' => $date,
            ];

            $payload = [
                ...$match,
                'market_value' => $market,
                'cash_value' => $cash,
                'total_value' => $totalValue,
                'net_cash_flow' => $cashFlow,
                'currency' => 'USD',
                'source' => 'demo',
                'metadata' => json_encode([
                    'demo' => true,
                    'holding_count' => 7,
                ]),
                'updated_at' => now(),
                'created_at' => now(),
            ];

            $this->upsertFlexible($table, $match, $payload);
        }

        $this->line('Seeded 13 months of historical portfolio valuations.');
    }

    private function seedBenchmarkHistory(): void
    {
        if (! Schema::hasTable('benchmarks') || ! Schema::hasTable('benchmark_prices')) {
            $this->warn('Benchmark tables not found; skipping benchmark demo history.');
            return;
        }

        $benchmark = DB::table('benchmarks')
            ->whereIn('symbol', ['VTI', 'SPY'])
            ->orderByRaw("CASE WHEN symbol = 'VTI' THEN 0 ELSE 1 END")
            ->first();

        if (! $benchmark) {
            $this->warn('No VTI/SPY benchmark found. Existing benchmark setup was left untouched.');
            return;
        }

        // Synthetic benchmark series for the demo date range.
        // It is intentionally stronger than the demo portfolio.
        $prices = [
            ['2025-08-31', 100.00],
            ['2025-09-30', 102.80],
            ['2025-10-31', 104.10],
            ['2025-11-30', 106.90],
            ['2025-12-31', 108.30],
            ['2026-01-31', 110.40],
            ['2026-02-28', 109.70],
            ['2026-03-31', 112.10],
            ['2026-04-30', 114.20],
            ['2026-05-31', 116.60],
            ['2026-06-30', 119.10],
            ['2026-07-31', 121.80],
            ['2026-08-13', 123.40],
        ];

        foreach ($prices as [$date, $price]) {
            $match = [
                'benchmark_id' => $benchmark->id,
                'price_date' => $date,
            ];

            $payload = [
                ...$match,
                'close_price' => $price,
                'adjusted_close_price' => $price,
                'source' => 'demo',
                'metadata' => json_encode(['demo' => true]),
                'updated_at' => now(),
                'created_at' => now(),
            ];

            // Never overwrite real benchmark history in production.
            $exists = DB::table('benchmark_prices')
                ->where('benchmark_id', $benchmark->id)
                ->where('price_date', $date)
                ->exists();

            if (! $exists) {
                DB::table('benchmark_prices')->insert(
                    $this->filterColumns('benchmark_prices', $payload)
                );
            }
        }

        $this->line("Seeded benchmark history against {$benchmark->symbol}.");
    }

    private function tx(
        int $accountId,
        ?int $securityId,
        string $type,
        string $date,
        float $quantity,
        float $price,
        float $grossAmount,
        float $fees,
        float $netAmount,
        string $description,
        array $extra = []
    ): array {
        return array_merge([
            'investment_account_id' => $accountId,
            'security_id' => $securityId,
            'transaction_type' => $type,
            'transaction_date' => $date,
            'settlement_date' => $date,
            'quantity' => $quantity,
            'price' => $price,
            'gross_amount' => $grossAmount,
            'fees' => $fees,
            'net_amount' => $netAmount,
            'currency' => 'USD',
            'description' => $description,
            'realized_gain_loss' => null,
            'holding_period_days' => null,
            'is_qualified_dividend' => false,
            'is_tax_exempt' => false,
            'tax_withheld' => 0,
            'metadata' => json_encode(['demo' => true]),
        ], $extra);
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function firstColumn(string $table, array $candidates): ?string
    {
        $columns = $this->columns($table);

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function columns(string $table): array
    {
        return $this->columnCache[$table]
            ??= Schema::getColumnListing($table);
    }

    private function filterColumns(string $table, array $data): array
    {
        $columns = array_flip($this->columns($table));

        return array_filter(
            $data,
            fn ($value, $key) => isset($columns[$key]),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function insertGetIdFlexible(string $table, array $data): int
    {
        return (int) DB::table($table)->insertGetId(
            $this->filterColumns($table, $data)
        );
    }

    private function updateFlexible(string $table, array $where, array $data): void
    {
        $query = DB::table($table);

        foreach ($this->filterColumns($table, $where) as $column => $value) {
            $query->where($column, $value);
        }

        $query->update($this->filterColumns($table, $data));
    }

    private function upsertFlexible(string $table, array $match, array $payload): void
    {
        $filteredMatch = $this->filterColumns($table, $match);

        if ($filteredMatch === []) {
            throw new RuntimeException("No usable match columns for {$table} upsert.");
        }

        $query = DB::table($table);

        foreach ($filteredMatch as $column => $value) {
            $query->where($column, $value);
        }

        $existing = $query->first();

        if ($existing) {
            $this->updateFlexible(
                $table,
                ['id' => $existing->id],
                $payload
            );
            return;
        }

        DB::table($table)->insert(
            $this->filterColumns($table, $payload)
        );
    }
}