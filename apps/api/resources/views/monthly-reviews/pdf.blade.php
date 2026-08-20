<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        Helmio Monthly Portfolio Review
    </title>

    <style>
        @page {
            margin: 38px 42px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #0f172a;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.55;
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        .header {
            padding: 24px;
            color: #ffffff;
            background: #0f172a;
            border-radius: 12px;
        }

        .brand {
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #93c5fd;
        }

        .title {
            margin: 12px 0 8px;
            font-size: 24px;
            line-height: 1.2;
        }

        .period {
            color: #cbd5e1;
            font-size: 11px;
        }

        .summary {
            margin-top: 16px;
            color: #e2e8f0;
            font-size: 11px;
            line-height: 1.7;
        }

        .section {
            margin-top: 22px;
        }

        .section-title {
            margin-bottom: 10px;
            font-size: 15px;
        }

        .metrics {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-left: -8px;
            margin-right: -8px;
        }

        .metrics td {
            width: 25%;
            padding: 12px;
            vertical-align: top;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .metric-label {
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .metric-value {
            margin-top: 5px;
            font-size: 16px;
            font-weight: bold;
        }

        .metric-note {
            margin-top: 3px;
            color: #64748b;
            font-size: 8px;
        }

        .assessment {
            margin-top: 18px;
            padding: 14px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .assessment-title {
            margin-bottom: 5px;
            color: #1d4ed8;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .assessment-text {
            margin: 0;
            color: #334155;
            font-size: 10px;
            line-height: 1.65;
        }

        .signal-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .signal-table td {
            width: 33.333%;
            padding: 7px 8px;
            color: #475569;
            background: #ffffff;
            border: 1px solid #dbeafe;
            font-size: 8px;
        }

        .priority-box {
            margin-top: 10px;
            padding: 11px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 7px;
        }

        .question {
            margin-bottom: 7px;
            padding: 9px 11px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            page-break-inside: avoid;
        }

        .event {
            margin-bottom: 10px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .event-heading {
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: bold;
        }

        .event-meta {
            margin-bottom: 7px;
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .event-summary {
            margin: 0;
            color: #334155;
        }

        .positive {
            background: #ecfdf5;
            border-color: #a7f3d0;
        }

        .attention {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .limitation {
            margin-bottom: 7px;
            padding: 9px 11px;
            color: #78350f;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 7px;
        }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    @php
        $keyChanges = collect($review->key_changes ?? []);
        $reviewItems = collect($review->review_items ?? []);
        $positiveChanges = collect($review->positive_changes ?? []);

        $severityRank = function (?string $severity): int {
            return match (strtolower((string) $severity)) {
                'critical' => 1,
                'high' => 2,
                'important' => 3,
                'medium', 'moderate' => 4,
                'low' => 5,
                'information', 'informational' => 6,
                'positive' => 7,
                default => 8,
            };
        };

        $topReviewItem = $reviewItems
            ->sortBy(fn ($item) =>
                $severityRank(
                    data_get($item, 'severity')
                )
            )
            ->first();

        $topChange = $keyChanges
            ->sortBy(fn ($item) =>
                $severityRank(
                    data_get($item, 'severity')
                )
            )
            ->first();

        $primaryConcern =
            $topReviewItem
            ?: (
                $topChange
                && in_array(
                    strtolower(
                        (string) data_get(
                            $topChange,
                            'severity',
                            'information'
                        )
                    ),
                    [
                        'critical',
                        'high',
                        'important',
                        'medium',
                        'moderate',
                    ],
                    true
                )
                    ? $topChange
                    : null
            );

        $primaryConcernHeadline =
            data_get(
                $primaryConcern,
                'headline'
            )
            ?? 'No high-priority issue detected';

        $primaryConcernSummary =
            data_get(
                $primaryConcern,
                'summary'
            )
            ?? (
                $primaryConcern
                    ? 'Review the supporting portfolio analysis.'
                    : 'Helmio did not identify a high-priority review item in this saved monthly review.'
            );

        $topPositive = $positiveChanges->first();

        $topPositiveText =
            is_array($topPositive)
                ? (
                    $topPositive['summary']
                    ?? $topPositive['message']
                    ?? $topPositive['headline']
                    ?? null
                )
                : $topPositive;

        $scoreChange = $review->helm_score_change;
        $valueChange = $review->portfolio_value_change;
        $annualCostChange = $review->annual_cost_change;

        $scoreDirection = match (true) {
            $scoreChange === null => 'not available',
            $scoreChange > 0 => 'improved',
            $scoreChange < 0 => 'declined',
            default => 'was unchanged',
        };

        $valueDirection = match (true) {
            $valueChange === null => 'not available',
            $valueChange > 0 => 'increased',
            $valueChange < 0 => 'decreased',
            default => 'was essentially unchanged',
        };

        $costDirection = match (true) {
            $annualCostChange === null => 'not available',
            $annualCostChange > 0 => 'increased',
            $annualCostChange < 0 => 'decreased',
            default => 'was unchanged',
        };

        $attentionCount = (int) (
            $review->attention_event_count
            ?? $reviewItems->count()
        );

        $monthlyAssessment = match (true) {
            $attentionCount >= 3 =>
                'Several items deserve attention this month. Start with the highest-priority issue before reviewing lower-impact changes.',

            $attentionCount > 0 && $scoreChange !== null && $scoreChange < 0 =>
                'Portfolio health weakened during the month and at least one item deserves review.',

            $attentionCount > 0 =>
                'The portfolio had review-worthy activity this month. Focus first on the highest-priority item below.',

            $scoreChange !== null && $scoreChange > 0 =>
                'Portfolio health improved during the month and no high-priority review item was saved.',

            default =>
                'No high-priority issue was saved for this month. Review the trend summary and material changes below.',
        };

        $questionByCategory = [
            'cost' =>
                'Can you walk me through every layer of fees I paid this month and explain whether any can be reduced?',
            'fees' =>
                'Can you walk me through every layer of fees I paid this month and explain whether any can be reduced?',
            'performance' =>
                'What specifically drove my portfolio performance this month, and how did it compare with the appropriate benchmark?',
            'risk' =>
                'Has the portfolio’s risk changed, and is the current level still appropriate for my goals and time horizon?',
            'diversification' =>
                'Where is my portfolio most concentrated today, and what risk does that concentration create?',
            'concentration' =>
                'Where is my portfolio most concentrated today, and what risk does that concentration create?',
            'trading' =>
                'What was the purpose of the trading this month, and what costs or tax consequences did it create?',
            'tax' =>
                'Did any activity this month create avoidable tax drag or tax-planning opportunities?',
            'suitability' =>
                'Does my current portfolio still match my stated objectives, time horizon, liquidity needs, and risk tolerance?',
            'cash' =>
                'Is my current cash level intentional, and is it helping or hurting the portfolio’s objectives?',
        ];

        $advisorQuestions = $reviewItems
            ->concat($keyChanges)
            ->map(function ($item) use ($questionByCategory) {
                $category = strtolower(
                    (string) data_get(
                        $item,
                        'category',
                        ''
                    )
                );

                return $questionByCategory[$category]
                    ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->take(4);

        if ($advisorQuestions->isEmpty()) {
            $advisorQuestions = collect([
                'What changed most in my portfolio this month, and was that change intentional?',
                'Is there anything in this review that deserves action before our next regular meeting?',
                'What should I expect to see next month if the portfolio is behaving as intended?',
            ]);
        }
    @endphp

    <section class="header">
        <div class="brand">
            Helmio Investment Oversight
        </div>

        <h1 class="title">
            {{ $review->headline
                ?: 'Monthly Portfolio Review' }}
        </h1>

        <p class="period">
            {{ $review->period_start->format('F j, Y') }}
            through
            {{ $review->period_end->format('F j, Y') }}
        </p>

        <p class="summary">
            {{ $review->summary }}
        </p>
    </section>

    <section class="assessment">
        <div class="assessment-title">
            What matters this month
        </div>

        <p class="assessment-text">
            {{ $monthlyAssessment }}
        </p>

        <table class="signal-table">
            <tr>
                <td>
                    Portfolio value {{ $valueDirection }}
                </td>
                <td>
                    Helm Score {{ $scoreDirection }}
                </td>
                <td>
                    Annual cost {{ $costDirection }}
                </td>
            </tr>
        </table>

        <div class="priority-box">
            <strong>Priority:</strong>
            {{ $primaryConcernHeadline }}

            <br>

            <span>
                {{ $primaryConcernSummary }}
            </span>
        </div>

        @if ($topPositiveText)
            <p class="assessment-text" style="margin-top: 10px;">
                <strong>Positive signal:</strong>
                {{ $topPositiveText }}
            </p>
        @endif
    </section>

    <section class="section">
        <h2 class="section-title">
            Monthly overview
        </h2>

        <table class="metrics">
            <tr>
                <td>
                    <div class="metric-label">
                        Ending value
                    </div>

                    <div class="metric-value">
                        @if ($review->ending_portfolio_value !== null)
                            ${{ number_format(
                                (float) $review->ending_portfolio_value,
                                2
                            ) }}
                        @else
                            —
                        @endif
                    </div>
                </td>

                <td>
                    <div class="metric-label">
                        Value change
                    </div>

                    <div class="metric-value">
                        @if ($review->portfolio_value_change !== null)
                            {{ $review->portfolio_value_change >= 0 ? '+' : '-' }}
                            ${{ number_format(
                                abs((float) $review->portfolio_value_change),
                                2
                            ) }}
                        @else
                            —
                        @endif
                    </div>

                    @if ($review->portfolio_value_change_rate !== null)
                        <div class="metric-note">
                            {{ $review->portfolio_value_change_rate >= 0 ? '+' : '' }}
                            {{ number_format(
                                (float) $review->portfolio_value_change_rate * 100,
                                2
                            ) }}%
                        </div>
                    @endif
                </td>

                <td>
                    <div class="metric-label">
                        Helm Score
                    </div>

                    <div class="metric-value">
                        {{ $review->ending_helm_score ?? '—' }}
                    </div>

                    @if ($review->helm_score_change !== null)
                        <div class="metric-note">
                            {{ $review->helm_score_change >= 0 ? '+' : '' }}
                            {{ $review->helm_score_change }}
                            during period
                        </div>
                    @endif
                </td>

                <td>
                    <div class="metric-label">
                        Audit grade
                    </div>

                    <div class="metric-value">
                        {{ $review->ending_audit_grade ?? '—' }}
                    </div>

                    @if (
                        $review->starting_audit_grade
                        && $review->starting_audit_grade
                            !== $review->ending_audit_grade
                    )
                        <div class="metric-note">
                            From
                            {{ $review->starting_audit_grade }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </section>

    <section class="section">
        <h2 class="section-title">
            Activity summary
        </h2>

        <table class="metrics">
            <tr>
                <td>
                    <div class="metric-label">
                        Timeline events
                    </div>

                    <div class="metric-value">
                        {{ $review->event_count }}
                    </div>
                </td>

                <td>
                    <div class="metric-label">
                        Positive changes
                    </div>

                    <div class="metric-value">
                        {{ $review->positive_event_count }}
                    </div>
                </td>

                <td>
                    <div class="metric-label">
                        Items to review
                    </div>

                    <div class="metric-value">
                        {{ $review->attention_event_count }}
                    </div>
                </td>

                <td>
                    <div class="metric-label">
                        Ending annual cost
                    </div>

                    <div class="metric-value">
                        @if ($review->ending_annual_cost !== null)
                            ${{ number_format(
                                (float) $review->ending_annual_cost,
                                2
                            ) }}
                        @else
                            —
                        @endif
                    </div>

                    @if ($review->annual_cost_change !== null)
                        <div class="metric-note">
                            {{ $review->annual_cost_change >= 0 ? '+' : '-' }}
                            ${{ number_format(
                                abs((float) $review->annual_cost_change),
                                2
                            ) }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </section>

    <section class="section">
        <h2 class="section-title">
            Key changes
        </h2>

        @forelse ($review->key_changes ?? [] as $change)
            <article class="event">
                <div class="event-meta">
                    {{ str($change['severity'] ?? 'information')->title() }}
                    ·
                    {{ str($change['category'] ?? 'portfolio')
                        ->replace('_', ' ')
                        ->title() }}

                    @if (! empty($change['event_date']))
                        ·
                        {{ \Carbon\Carbon::parse(
                            $change['event_date']
                        )->format('M j, Y') }}
                    @endif
                </div>

                <div class="event-heading">
                    {{ $change['headline']
                        ?? 'Portfolio change' }}
                </div>

                <p class="event-summary">
                    {{ $change['summary']
                        ?? 'No additional explanation was recorded.' }}
                </p>
            </article>
        @empty
            <p>
                No material changes were detected during this period.
            </p>
        @endforelse
    </section>

    @if (! empty($review->review_items))
        <section class="section">
            <h2 class="section-title">
                Items to review
            </h2>

            @foreach ($review->review_items as $item)
                <article class="event attention">
                    <div class="event-meta">
                        {{ str($item['severity'] ?? 'medium')->title() }}
                        ·
                        {{ str($item['category'] ?? 'portfolio')
                            ->replace('_', ' ')
                            ->title() }}
                    </div>

                    <div class="event-heading">
                        {{ $item['headline']
                            ?? 'Review item' }}
                    </div>

                    <p class="event-summary">
                        {{ $item['summary']
                            ?? 'Review the supporting portfolio analysis.' }}
                    </p>
                </article>
            @endforeach
        </section>
    @endif

    @if (! empty($review->positive_changes))
        <section class="section">
            <h2 class="section-title">
                Positive developments
            </h2>

            @foreach ($review->positive_changes as $change)
                <article class="event positive">
                    <p class="event-summary">
                        {{ $change }}
                    </p>
                </article>
            @endforeach
        </section>
    @endif

    <section class="section">
        <h2 class="section-title">
            Questions worth asking your advisor
        </h2>

        <p style="color: #64748b; margin-bottom: 10px;">
            These questions are selected from the categories that appeared in
            this month’s saved review items and material changes.
        </p>

        @foreach ($advisorQuestions as $question)
            <div class="question">
                {{ $question }}
            </div>
        @endforeach
    </section>

    @if (! empty($review->limitations))
        <section class="section">
            <h2 class="section-title">
                Data and review limitations
            </h2>

            @foreach ($review->limitations as $limitation)
                <div class="limitation">
                    {{ $limitation }}
                </div>
            @endforeach
        </section>
    @endif

    <footer class="footer">
        <p>
            Prepared for
            {{ $user->name }}
            on
            {{ $generatedAt->format('F j, Y g:i A') }}.
        </p>

        <p>
            Helmio provides investment-monitoring and educational analysis.
            This report does not constitute individualized investment, tax,
            accounting or legal advice. Underlying brokerage data may be
            delayed or incomplete.
        </p>
    </footer>
</body>
</html>