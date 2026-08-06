<?php

namespace App\Http\Controllers;

use App\Models\MonthlyPortfolioReview;
use App\Services\Portfolio\MonthlyPortfolioReviewService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MonthlyPortfolioReviewController extends Controller
{
    public function index(
        Request $request,
    ): View {
        $reviews = MonthlyPortfolioReview::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('period_start')
            ->paginate(12);

        return view('monthly-reviews.index', [
            'reviews' => $reviews,
        ]);
    }

    public function generate(
        Request $request,
        MonthlyPortfolioReviewService $service,
    ): RedirectResponse {
        $validated = $request->validate([
            'month' => [
                'nullable',
                'date_format:Y-m',
            ],
        ]);

        $month = isset($validated['month'])
            ? CarbonImmutable::createFromFormat(
                'Y-m',
                $validated['month'],
            )->startOfMonth()
            : CarbonImmutable::now()->startOfMonth();

        $review = $service->generate(
            $request->user(),
            $month,
        );

        return redirect()
            ->route(
                'monthly-reviews.show',
                $review,
            )
            ->with(
                'success',
                'Monthly portfolio review generated.',
            );
    }

    public function show(
        Request $request,
        MonthlyPortfolioReview $monthlyPortfolioReview,
    ): View {
        $this->authorizeReview(
            $request,
            $monthlyPortfolioReview,
        );

        return view('monthly-reviews.show', [
            'review' => $monthlyPortfolioReview,
        ]);
    }

    public function pdf(
        Request $request,
        MonthlyPortfolioReview $monthlyPortfolioReview,
    ): Response {
        $this->authorizeReview(
            $request,
            $monthlyPortfolioReview,
        );

        $monthlyPortfolioReview->loadMissing('user');

        $filename = sprintf(
            'helmio-monthly-review-%s.pdf',
            $monthlyPortfolioReview
                ->period_start
                ->format('Y-m'),
        );

        $pdf = Pdf::loadView(
            'monthly-reviews.pdf',
            [
                'review' =>
                    $monthlyPortfolioReview,

                'user' =>
                    $request->user(),

                'generatedAt' =>
                    now(),
            ],
        )->setPaper('letter', 'portrait');

        return $pdf->download($filename);
    }

    private function authorizeReview(
        Request $request,
        MonthlyPortfolioReview $review,
    ): void {
        abort_unless(
            $review->user_id
                === $request->user()->id,
            403,
        );
    }
}