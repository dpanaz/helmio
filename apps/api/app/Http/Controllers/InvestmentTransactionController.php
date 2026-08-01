<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use App\Models\InvestmentTransaction;
use App\Models\Security;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentTransactionController extends Controller
{
    public function index(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): View {
        abort_unless(
            $investmentAccount->user_id === $request->user()->id,
            403,
        );

        $transactions = $investmentAccount
            ->transactions()
            ->with('security')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25);

        $summary = [
            'deposits' => $investmentAccount->transactions()
                ->where('transaction_type', 'deposit')
                ->sum('net_amount'),

            'withdrawals' => abs(
                $investmentAccount->transactions()
                    ->where('transaction_type', 'withdrawal')
                    ->sum('net_amount')
            ),

            'fees' => $investmentAccount->transactions()
                ->sum('fees'),

            'tradeCount' => $investmentAccount->transactions()
                ->whereIn('transaction_type', ['buy', 'sell'])
                ->count(),
        ];

        return view('transactions.index', [
            'account' => $investmentAccount,
            'transactions' => $transactions,
            'summary' => $summary,
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

        return view('transactions.create', [
            'account' => $investmentAccount,
            'securities' => Security::query()
                ->orderBy('name')
                ->get(),
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
            'transaction_type' => [
                'required',
                'in:buy,sell,deposit,withdrawal,dividend,interest,fee,transfer_in,transfer_out,distribution,tax,other',
            ],
            'transaction_date' => ['required', 'date'],
            'settlement_date' => ['nullable', 'date'],
            'security_id' => ['nullable', 'exists:securities,id'],
            'quantity' => ['nullable', 'numeric'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'gross_amount' => ['required', 'numeric'],
            'fees' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $grossAmount = (float) $validated['gross_amount'];
        $fees = (float) ($validated['fees'] ?? 0);

        $outflowTypes = [
            'buy',
            'withdrawal',
            'fee',
            'transfer_out',
            'tax',
        ];

        if (in_array($validated['transaction_type'], $outflowTypes, true)) {
            $grossAmount = -abs($grossAmount);
        } else {
            $grossAmount = abs($grossAmount);
        }

        $netAmount = $grossAmount - $fees;

        InvestmentTransaction::create([
            'investment_account_id' => $investmentAccount->id,
            'security_id' => $validated['security_id'] ?? null,
            'transaction_type' => $validated['transaction_type'],
            'transaction_date' => $validated['transaction_date'],
            'settlement_date' => $validated['settlement_date'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'price' => $validated['price'] ?? null,
            'gross_amount' => $grossAmount,
            'fees' => $fees,
            'net_amount' => $netAmount,
            'description' => $validated['description'] ?? null,
            'metadata' => [
                'entry_method' => 'manual',
            ],
        ]);

        return redirect()
            ->route('accounts.transactions.index', $investmentAccount)
            ->with('success', 'Transaction added successfully.');
    }
}
