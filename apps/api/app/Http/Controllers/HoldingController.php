<?php

namespace App\Http\Controllers;

use App\Models\Holding;
use App\Models\InvestmentAccount;
use App\Models\Security;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HoldingController extends Controller
{
    public function index(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): View {
        abort_unless(
            $investmentAccount->user_id === $request->user()->id,
            403,
        );

        $holdings = $investmentAccount
            ->holdings()
            ->with('security')
            ->orderByDesc('market_value')
            ->get();

        return view('holdings.index', [
            'account' => $investmentAccount,
            'holdings' => $holdings,
            'totalValue' => $holdings->sum('market_value'),
        ]);
    }

    public function create(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): View {
        abort_unless(
            $investmentAccount->user_id === $request->user()->id,
            403,
        );

        return view('holdings.create', [
            'account' => $investmentAccount,
        ]);
    }

    public function store(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): RedirectResponse {
        abort_unless(
            $investmentAccount->user_id === $request->user()->id,
            403,
        );

        $validated = $request->validate([
            'symbol' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:150'],
            'security_type' => [
                'required',
                'in:stock,etf,mutual_fund,bond,cash,option,crypto,annuity,other',
            ],
            'asset_class' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:100'],
            'expense_ratio' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_basis' => ['nullable', 'numeric', 'min:0'],
        ]);

        $symbol = filled($validated['symbol'] ?? null)
            ? strtoupper($validated['symbol'])
            : null;

        $security = Security::firstOrCreate(
            [
                'symbol' => $symbol,
                'security_type' => $validated['security_type'],
            ],
            [
                'name' => $validated['name'],
                'asset_class' => $validated['asset_class'] ?? null,
                'sector' => $validated['sector'] ?? null,
                'expense_ratio' => $validated['expense_ratio'] ?? null,
                'last_price' => $validated['price'],
                'price_as_of' => now(),
            ],
        );

        $marketValue = round(
            (float) $validated['quantity'] * (float) $validated['price'],
            2,
        );

        Holding::updateOrCreate(
            [
                'investment_account_id' => $investmentAccount->id,
                'security_id' => $security->id,
                'as_of_date' => now()->toDateString(),
            ],
            [
                'quantity' => $validated['quantity'],
                'price' => $validated['price'],
                'market_value' => $marketValue,
                'cost_basis' => $validated['cost_basis'] ?? null,
                'unrealized_gain_loss' => isset($validated['cost_basis'])
                    ? $marketValue - (float) $validated['cost_basis']
                    : null,
                'metadata' => [
                    'entry_method' => 'manual',
                ],
            ],
        );

        $investmentAccount->update([
            'current_value' => $investmentAccount
                ->holdings()
                ->whereDate('as_of_date', now()->toDateString())
                ->sum('market_value'),
        ]);

        return redirect()
            ->route('accounts.holdings.index', $investmentAccount)
            ->with('success', 'Holding added successfully.');
    }
}
