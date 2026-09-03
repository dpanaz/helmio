<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class MarketingPageController extends Controller
{
    public function advisorCheck(): View
    {
        return view(
            'marketing.advisor-check',
        );
    }

    public function advisorFees(): View
    {
        return view(
            'marketing.advisor-fees',
        );
    }

    public function advisorPerformance(): View
    {
        return view(
            'marketing.financial-advisor-performance',
        );
    }

    public function portfolioChurning(): View
    {
        return view(
            'marketing.portfolio-churning',
        );
    }

    public function portfolioDiversification(): View
    {
        return view(
            'marketing.portfolio-diversification',
        );
    }

    public function portfolioRisk(): View
    {
        return view(
            'marketing.portfolio-risk',
        );
    }

    public function taxEfficiency(): View
    {
        return view(
            'marketing.tax-efficiency',
        );
    }

    public function howItWorks(): View
    {
        return view(
            'marketing.how-it-works',
        );
    }

    public function security(): View
    {
        return view(
            'marketing.security',
        );
    }
}