<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InvestmentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentAccountController extends Controller
{
    public function index(Request $request): View
    {
        $accounts = InvestmentAccount::query()
            ->where('user_id', $request->user()->id)
            ->with('institution')
            ->latest()
            ->get();

        return view('accounts.index', [
            'accounts' => $accounts,
            'totalValue' => $accounts->sum('current_value'),
            'totalCash' => $accounts->sum('cash_value'),
        ]);
    }

    public function create(): View
    {
        return view('accounts.create', [
            'institutions' => Institution::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'institution_name' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'account_type' => [
                'required',
                'in:individual,joint,traditional_ira,roth_ira,sep_ira,401k,403b,trust,529,other',
            ],
            'account_number_mask' => ['nullable', 'string', 'max:8'],
            'current_value' => ['required', 'numeric', 'min:0'],
            'cash_value' => ['nullable', 'numeric', 'min:0'],
            'annual_advisory_fee_rate' => [
    'nullable',
    'numeric',
    'min:0',
    'max:100',
],
'annual_account_fee' => [
    'nullable',
    'numeric',
    'min:0',
],
'advisory_fee_applies_to_cash' => [
    'nullable',
    'boolean',
],
        ]);

        $institutionId = $validated['institution_id'] ?? null;

        if (! $institutionId && ! empty($validated['institution_name'])) {
            $institution = Institution::firstOrCreate(
                ['slug' => str($validated['institution_name'])->slug()->toString()],
                [
                    'name' => $validated['institution_name'],
                    'is_active' => true,
                ],
            );

            $institutionId = $institution->id;
        }

        InvestmentAccount::create([
            'user_id' => $request->user()->id,
            'institution_id' => $institutionId,
            'name' => $validated['name'],
            'account_type' => $validated['account_type'],
            'account_number_mask' => $validated['account_number_mask'] ?? null,
            'current_value' => $validated['current_value'],
            'cash_value' => $validated['cash_value'] ?? 0,
            'status' => 'active',
            'last_synced_at' => now(),
            'metadata' => [
                'entry_method' => 'manual',
            ],
            'annual_advisory_fee_rate' =>
    isset($validated['annual_advisory_fee_rate'])
        ? (float) $validated['annual_advisory_fee_rate'] / 100
        : null,

'annual_account_fee' =>
    $validated['annual_account_fee'] ?? 0,

'advisory_fee_applies_to_cash' =>
    $request->boolean('advisory_fee_applies_to_cash'),
        ]);

        return redirect()
            ->route('accounts.index')
            ->with('success', 'Investment account added successfully.');
    }
}
