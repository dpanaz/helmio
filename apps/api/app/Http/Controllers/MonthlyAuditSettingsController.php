<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MonthlyAuditSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('audit.monthly-settings', [
            'user' => $request->user(),
            'timezones' => [
                'America/New_York' => 'Eastern Time',
                'America/Chicago' => 'Central Time',
                'America/Denver' => 'Mountain Time',
                'America/Los_Angeles' => 'Pacific Time',
                'America/Phoenix' => 'Arizona',
                'America/Anchorage' => 'Alaska',
                'Pacific/Honolulu' => 'Hawaii',
                'UTC' => 'UTC',
            ],
        ]);
    }

    public function update(
        Request $request,
    ): RedirectResponse {
        $validated = $request->validate([
            'monthly_audit_email' => [
                'nullable',
                'email:rfc',
                'max:255',
            ],

            'monthly_audit_day' => [
                'required',
                'integer',
                'between:1,28',
            ],

            'monthly_audit_time' => [
                'required',
                'date_format:H:i',
            ],

            'timezone' => [
                'required',
                Rule::in([
                    'America/New_York',
                    'America/Chicago',
                    'America/Denver',
                    'America/Los_Angeles',
                    'America/Phoenix',
                    'America/Anchorage',
                    'Pacific/Honolulu',
                    'UTC',
                ]),
            ],
        ]);

        $request->user()->update([
            'monthly_audit_enabled' =>
                $request->boolean('monthly_audit_enabled'),

            'monthly_audit_email' =>
                $validated['monthly_audit_email']
                ?: $request->user()->email,

            'monthly_audit_day' =>
                $validated['monthly_audit_day'],

            'monthly_audit_time' =>
                $validated['monthly_audit_time'],

            'timezone' =>
                $validated['timezone'],
        ]);

        return back()->with(
            'success',
            'Monthly report settings updated.',
        );
    }
}
