<?php

namespace App\Http\Controllers;

use App\Models\Benchmark;
use App\Models\BenchmarkReturn;
use App\Models\InvestmentAccount;
use App\Models\PortfolioSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceDataController extends Controller
{
    public function index(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): View {
        $this->authorizeAccount($request, $investmentAccount);

        $investmentAccount->load([
            'benchmark',
            'portfolioSnapshots' => fn ($query) =>
                $query->orderByDesc('snapshot_date'),
        ]);

        return view('performance-data.index', [
            'account' => $investmentAccount,
            'benchmarks' => Benchmark::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeSnapshot(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): RedirectResponse {
        $this->authorizeAccount($request, $investmentAccount);

        $validated = $request->validate([
            'snapshot_date' => [
                'required',
                'date',
            ],
            'ending_value' => [
                'required',
                'numeric',
                'min:0',
            ],
            'cash_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'external_cash_flow' => [
                'nullable',
                'numeric',
            ],
        ]);

        PortfolioSnapshot::updateOrCreate(
            [
                'investment_account_id' =>
                    $investmentAccount->id,
                'snapshot_date' =>
                    $validated['snapshot_date'],
            ],
            [
                'ending_value' =>
                    $validated['ending_value'],
                'cash_value' =>
                    $validated['cash_value'] ?? 0,
                'external_cash_flow' =>
                    $validated['external_cash_flow'] ?? 0,
                'source' => 'manual',
            ],
        );

        return back()->with(
            'success',
            'Portfolio snapshot saved.',
        );
    }

    public function assignBenchmark(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): RedirectResponse {
        $this->authorizeAccount($request, $investmentAccount);

        $validated = $request->validate([
            'benchmark_id' => [
                'nullable',
                'exists:benchmarks,id',
            ],
        ]);

        $investmentAccount->update([
            'benchmark_id' =>
                $validated['benchmark_id'] ?? null,
        ]);

        return back()->with(
            'success',
            'Account benchmark updated.',
        );
    }

    public function storeBenchmark(
        Request $request,
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'symbol' => [
                'nullable',
                'string',
                'max:30',
                'unique:benchmarks,symbol',
            ],
            'benchmark_type' => [
                'required',
                'in:index,blended,custom',
            ],
        ]);

        Benchmark::create([
            ...$validated,
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Benchmark created.',
        );
    }

    public function storeBenchmarkReturn(
        Request $request,
        Benchmark $benchmark,
    ): RedirectResponse {
        $validated = $request->validate([
            'return_date' => [
                'required',
                'date',
            ],
            'period_return' => [
                'required',
                'numeric',
                'min:-100',
                'max:1000',
            ],
            'period_type' => [
                'required',
                'in:daily,monthly,quarterly,annual',
            ],
        ]);

        BenchmarkReturn::updateOrCreate(
            [
                'benchmark_id' => $benchmark->id,
                'return_date' =>
                    $validated['return_date'],
                'period_type' =>
                    $validated['period_type'],
            ],
            [
                'period_return' =>
                    (float) $validated['period_return'] / 100,
                'source' => 'manual',
            ],
        );

        return back()->with(
            'success',
            'Benchmark return saved.',
        );
    }

    private function authorizeAccount(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): void {
        abort_unless(
            $investmentAccount->user_id
                === $request->user()->id,
            403,
        );
    }
}
