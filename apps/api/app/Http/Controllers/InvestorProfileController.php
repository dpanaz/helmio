<?php

namespace App\Http\Controllers;

use App\Models\InvestorProfile;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvestorProfileController extends Controller
{
    public function edit(
        Request $request,
    ): View {
        $profile = InvestorProfile::firstOrCreate(
            [
                'user_id' =>
                    $request->user()->id,
            ],
            [
                'risk_tolerance' =>
                    InvestorProfile::RISK_MODERATE,

                'primary_objective' =>
                    InvestorProfile::OBJECTIVE_GROWTH,

                'planned_retirement_age' =>
                    65,

                'investment_experience' =>
                    'intermediate',
            ],
        );

        return view(
            'investor-profile.edit',
            [
                'profile' =>
                    $profile,

                'riskOptions' =>
                    InvestorProfile::riskToleranceOptions(),

                'objectiveOptions' =>
                    InvestorProfile::objectiveOptions(),

                'experienceOptions' => [
                    'beginner' =>
                        'Beginner',

                    'intermediate' =>
                        'Intermediate',

                    'advanced' =>
                        'Advanced',
                ],

                'liquidityOptions' => [
                    'low' =>
                        'Low',

                    'moderate' =>
                        'Moderate',

                    'high' =>
                        'High',
                ],

                'returnTo' =>
                    $this->safeReturnPath(
                        $request,
                    ),
            ],
        );
    }

    public function update(
        Request $request,
        DashboardService $dashboardService,
    ): RedirectResponse {
        $validated = $request->validate([
            'date_of_birth' => [
                'nullable',
                'date',
            ],

            'planned_retirement_age' => [
                'nullable',
                'integer',
                'min:40',
                'max:90',
            ],

            'employment_status' => [
                'nullable',
                'string',
                'max:50',
            ],

            'annual_income' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'estimated_net_worth' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax_bracket' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'investment_experience' => [
                'nullable',
                'string',
                'max:30',
            ],

            'primary_objective' => [
                'nullable',
                'string',
                'max:40',
            ],

            'time_horizon_years' => [
                'nullable',
                'integer',
                'min:1',
                'max:60',
            ],

            'risk_tolerance' => [
                'nullable',
                'string',
                'max:40',
            ],

            'liquidity_needs' => [
                'nullable',
                'string',
                'max:40',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'return_to' => [
                'nullable',
                'string',
                'max:2048',
            ],
        ]);

        $returnTo = $this->safeReturnPath(
            $request,
        );

        unset(
            $validated['return_to'],
        );

        InvestorProfile::updateOrCreate(
            [
                'user_id' =>
                    $request->user()->id,
            ],
            $validated,
        );

        $dashboardService->clearAdvisorAuditCache(
            $request->user()->id,
        );

        if ($returnTo !== null) {
            return redirect()
                ->to($returnTo)
                ->with(
                    'success',
                    'Investor profile updated successfully.',
                );
        }

        return redirect()
            ->route(
                'investor-profile.edit',
            )
            ->with(
                'success',
                'Investor profile updated successfully.',
            );
    }

    private function safeReturnPath(
        Request $request,
    ): ?string {
        $returnTo = trim(
            $request->string(
                'return_to',
            )->toString(),
        );

        if ($returnTo === '') {
            return null;
        }

        if (
            Str::startsWith(
                $returnTo,
                '/',
            )
            && ! Str::startsWith(
                $returnTo,
                '//',
            )
        ) {
            return $returnTo;
        }

        $appUrl = rtrim(
            (string) config(
                'app.url',
            ),
            '/',
        );

        if (
            $appUrl !== ''
            && Str::startsWith(
                $returnTo,
                $appUrl.'/',
            )
        ) {
            return $returnTo;
        }

        return null;
    }
}