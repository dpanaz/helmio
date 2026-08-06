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
                        Audit score
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