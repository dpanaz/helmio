<?php

namespace Database\Seeders;

use App\Models\Benchmark;
use Illuminate\Database\Seeder;

class BenchmarkSeeder extends Seeder
{
    public function run(): void
    {
        $benchmarks = [
            [
                'name' => 'S&P 500',
                'symbol' => 'SPY',
                'description' =>
                    'Large-cap United States equity benchmark.',
                'benchmark_type' => 'market_index',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Total United States Stock Market',
                'symbol' => 'VTI',
                'description' =>
                    'Broad United States equity market benchmark.',
                'benchmark_type' => 'market_index',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Nasdaq 100',
                'symbol' => 'QQQ',
                'description' =>
                    'Large non-financial companies listed on Nasdaq.',
                'benchmark_type' => 'market_index',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => '60/40 Portfolio',
                'symbol' => 'HELMIO-60-40',
                'description' =>
                    'A blended portfolio of 60% stocks and 40% bonds.',
                'benchmark_type' => 'blended',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($benchmarks as $benchmark) {
            Benchmark::query()->updateOrCreate(
                [
                    'symbol' => $benchmark['symbol'],
                ],
                $benchmark
            );
        }
    }
}