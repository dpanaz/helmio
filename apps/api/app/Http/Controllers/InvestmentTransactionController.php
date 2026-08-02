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
        $this->authorizeAccount(
            $request,
            $investmentAccount,
        );

        $transactions = $investmentAccount
            ->transactions()
            ->with('security')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(25);

        $summary = [
            'deposits' => $investmentAccount
                ->transactions()
                ->where('transaction_type', 'deposit')
                ->sum('net_amount'),

            'withdrawals' => abs(
                (float) $investmentAccount
                    ->transactions()
                    ->where('transaction_type', 'withdrawal')
                    ->sum('net_amount'),
            ),

            'fees' => $investmentAccount
                ->transactions()
                ->sum('fees'),

            'tradeCount' => $investmentAccount
                ->transactions()
                ->whereIn('transaction_type', [
                    'buy',
                    'sell',
                ])
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
        $this->authorizeAccount(
            $request,
            $investmentAccount,
        );

        return view('transactions.create', [
            'account' => $investmentAccount,

            'securities' => Security::query()
                ->orderByRaw(
                    'CASE WHEN symbol IS NULL THEN 1 ELSE 0 END',
                )
                ->orderBy('symbol')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(
        Request $request,
        InvestmentAccount $investmentAccount,
    ): RedirectResponse {
        $this->authorizeAccount(
            $request,
            $investmentAccount,
        );

        $validated = $request->validate([
            'transaction_type' => [
                'required',
                'in:buy,sell,deposit,withdrawal,dividend,interest,fee,transfer_in,transfer_out,distribution,tax,other',
            ],

            'transaction_date' => [
                'required',
                'date',
            ],

            'settlement_date' => [
                'nullable',
                'date',
                'after_or_equal:transaction_date',
            ],

            'security_id' => [
                'nullable',
                'exists:securities,id',
            ],

            'quantity' => [
                'nullable',
                'numeric',
            ],

            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'gross_amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'fees' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'realized_gain_loss' => [
                'nullable',
                'numeric',
            ],

            'holding_period_days' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_qualified_dividend' => [
                'nullable',
                'boolean',
            ],

            'is_tax_exempt' => [
                'nullable',
                'boolean',
            ],

            'tax_withheld' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $transactionType =
            $validated['transaction_type'];

        $grossAmount =
            (float) $validated['gross_amount'];

        $fees =
            (float) ($validated['fees'] ?? 0);

        $taxWithheld =
            (float) ($validated['tax_withheld'] ?? 0);

        $outflowTypes = [
            'buy',
            'withdrawal',
            'fee',
            'transfer_out',
            'tax',
        ];

        if (
            in_array(
                $transactionType,
                $outflowTypes,
                true,
            )
        ) {
            $grossAmount = -abs($grossAmount);
        } else {
            $grossAmount = abs($grossAmount);
        }

        /*
         * Net amount convention:
         *
         * Inflows:
         * gross - fees - tax withheld
         *
         * Outflows:
         * negative gross - fees - tax withheld
         */
        $netAmount =
            $grossAmount
            - $fees
            - $taxWithheld;

        InvestmentTransaction::create([
            'investment_account_id' =>
                $investmentAccount->id,

            'security_id' =>
                $validated['security_id'] ?? null,

            'transaction_type' =>
                $transactionType,

            'transaction_date' =>
                $validated['transaction_date'],

            'settlement_date' =>
                $validated['settlement_date'] ?? null,

            'quantity' =>
                $validated['quantity'] ?? null,

            'price' =>
                $validated['price'] ?? null,

            'gross_amount' =>
                $grossAmount,

            'fees' =>
                $fees,

            'net_amount' =>
                $netAmount,

            'realized_gain_loss' =>
                $validated['realized_gain_loss'] ?? null,

            'holding_period_days' =>
                $validated['holding_period_days'] ?? null,

            'is_qualified_dividend' =>
                $request->boolean(
                    'is_qualified_dividend',
                ),

            'is_tax_exempt' =>
                $request->boolean(
                    'is_tax_exempt',
                ),

            'tax_withheld' =>
                $taxWithheld,

            'description' =>
                $validated['description'] ?? null,

            'metadata' => [
                'entry_method' => 'manual',
            ],
        ]);

        return redirect()
            ->route(
                'accounts.transactions.index',
                $investmentAccount,
            )
            ->with(
                'success',
                'Transaction added successfully.',
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