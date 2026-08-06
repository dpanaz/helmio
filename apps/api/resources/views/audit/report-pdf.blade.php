<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Helmio Advisor Audit Report</title>

    <style>
        @page {
            margin: 42px 42px 54px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #0f172a;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            line-height: 1.5;
        }

        h1,
        h2,
        h3,
        h4,
        p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        .cover {
            min-height: 690px;
            padding: 42px;
            color: #0f172a;
            background: #ffffff;
            border-top: 8px solid #163A5F;
            border: 1px solid #d6dde8;
            border-radius: 10px;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1.5px;
        }

        .brand-subtitle {
            margin-top: 3px;
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1.4px;
        }

        .cover-title {
            margin-top: 86px;
            font-size: 30px;
            font-weight: bold;
            line-height: 1.15;
        }

        .cover-description {
            margin-top: 12px;
            max-width: 360px;
            color: #475569;
            font-size: 12px;
            line-height: 1.65;
        }

        .cover-score-table {
            margin-top: 64px;
        }

        .cover-score-table td {
            width: 33.333%;
            padding-right: 14px;
            vertical-align: top;
        }

        .cover-metric {
            min-height: 122px;
            padding: 18px;
            background: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 8px;
        }

        .cover-label {
            color: #2D7FF9;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .9px;
        }

        .cover-value {
            margin-top: 10px;
            font-size: 32px;
            font-weight: bold;
            line-height: 1;
        }

        .cover-value-small {
            margin-top: 12px;
            font-size: 20px;
            font-weight: bold;
            line-height: 1.2;
        }

        .cover-note {
            margin-top: 8px;
            color: #64748b;
            font-size: 8px;
        }

        .cover-footer {
            margin-top: 82px;
            padding-top: 20px;
            border-top: 1px solid #94a3b8;
        }

        .cover-footer td {
            width: 50%;
            vertical-align: bottom;
        }

        .cover-footer-label {
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .cover-footer-value {
            margin-top: 5px;
            color: #0f172a;
            font-size: 12px;
            font-weight: bold;
        }

        .cover-footer-right {
            text-align: right;
        }

        .report-header {
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .report-header td {
            vertical-align: middle;
        }

        .report-header-brand {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .report-header-title {
            color: #64748b;
            font-size: 8px;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .section {
            margin-top: 18px;
        }

        .section-heading {
            margin-bottom: 10px;
        }

        .section-eyebrow {
            color: #2563eb;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .9px;
        }

        .section-title {
            margin-top: 3px;
            font-size: 17px;
            font-weight: bold;
        }

        .section-copy {
            margin-top: 5px;
            max-width: 560px;
            color: #64748b;
            font-size: 9px;
            line-height: 1.6;
        }

        .hero-summary {
            padding: 20px;
            color: #0f172a;
            background: #ffffff;
            border-top: 8px solid #163A5F;
            border: 1px solid #d6dde8;
            border-radius: 8px;
        }

        .hero-summary td {
            vertical-align: top;
        }

        .hero-score {
            width: 28%;
            padding-right: 22px;
            border-right: 1px solid #94a3b8;
        }

        .hero-score-value {
            margin-top: 8px;
            font-size: 44px;
            font-weight: bold;
            line-height: 1;
        }

        .hero-score-label {
            margin-top: 8px;
            color: #475569;
            font-size: 10px;
        }

        .hero-narrative {
            width: 72%;
            padding-left: 22px;
        }

        .hero-headline {
            font-size: 15px;
            font-weight: bold;
        }

        .hero-copy {
            margin-top: 8px;
            color: #475569;
            font-size: 10px;
            line-height: 1.65;
        }

        .metric-table {
            margin-top: 12px;
        }

        .metric-table td {
            width: 25%;
            padding: 5px;
            vertical-align: top;
        }

        .metric-card {
            min-height: 70px;
            padding: 11px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .metric-card-red,
        .metric-card-amber,
        .metric-card-green {
            background: #f8fbff;
            border-top: 4px solid #2D7FF9;
            border-color: #d7e3f4;
        }

        .metric-label {
            color: #64748b;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .metric-value {
            margin-top: 6px;
            font-size: 17px;
            font-weight: bold;
        }

        .metric-caption {
            margin-top: 4px;
            color: #64748b;
            font-size: 7px;
        }

        .score-row {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .score-row td {
            vertical-align: middle;
        }

        .score-name {
            width: 22%;
            padding-right: 10px;
            font-weight: bold;
        }

        .score-track-cell {
            width: 58%;
        }

        .score-track {
            width: 100%;
            height: 9px;
            background: #eef5ff;
            border-radius: 5px;
        }

        .score-fill {
            height: 9px;
            background: #2D7FF9;
            border-radius: 5px;
        }

        .score-number {
            width: 10%;
            padding-left: 10px;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
        }

        .score-assessment {
            width: 10%;
            padding-left: 8px;
            color: #64748b;
            font-size: 8px;
            text-align: right;
        }

        .finding {
            margin-bottom: 10px;
            padding: 13px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .finding-critical,
        .finding-important,
        .finding-opportunity {
            background: #ffffff;
            border-color: #94a3b8;
        }

        .finding-critical {
            border-left: 6px solid #DC2626;
        }

        .finding-important {
            border-left: 6px solid #D97706;
        }

        .finding-opportunity {
            border-left: 6px solid #16A34A;
        }

        .finding-header td {
            vertical-align: top;
        }

        .finding-main {
            width: 76%;
        }

        .finding-impact {
            width: 24%;
            text-align: right;
        }

        .badge {
            display: inline-block;
            margin-right: 5px;
            padding: 3px 7px;
            color: #334155;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .badge-red,
        .badge-amber,
        .badge-green {
            color: #0f172a;
            background: #eef5ff;
        }

        .finding-title {
            margin-top: 8px;
            font-size: 12px;
            font-weight: bold;
        }

        .finding-copy {
            margin-top: 6px;
            color: #475569;
            line-height: 1.6;
        }

        .finding-impact-label {
            color: #64748b;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .finding-impact-value {
            margin-top: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        .recommendation {
            margin-top: 9px;
            padding: 9px;
            color: #334155;
            background: rgba(255, 255, 255, .72);
            border: 1px solid rgba(226, 232, 240, .8);
            border-radius: 6px;
        }

        .questions-box {
            padding: 16px;
            color: #0f172a;
            background: #ffffff;
            border-top: 8px solid #163A5F;
            border: 1px solid #d6dde8;
            border-radius: 8px;
        }

        .questions-title {
            font-size: 14px;
            font-weight: bold;
        }

        .question-item {
            margin-top: 10px;
            padding: 9px 10px;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .account-table,
        .detail-table {
            border: 1px solid #e2e8f0;
        }

        .account-table th,
        .account-table td,
        .detail-table th,
        .detail-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .account-table th,
        .detail-table th {
            color: #475569;
            background: #f8fafc;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .number {
            text-align: right !important;
        }

        .disclosure {
            padding: 14px;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .disclosure p + p {
            margin-top: 9px;
        }

        .footer {
            margin-top: 18px;
            padding-top: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            font-size: 7px;
        }

        @media print {
            body {
                color: #000000;
            }

            .cover,
            .hero-summary,
            .questions-box,
            .metric-card,
            .finding,
            .disclosure {
                box-shadow: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            .avoid-break,
            .finding,
            .metric-card,
            .score-row,
            .account-table tr,
            .detail-table tr {
                page-break-inside: avoid;
            }

            a {
                color: #000000;
                text-decoration: none;
            }
        }
    </style>
</head>

<body>
    @php
        /*
         * Support both the legacy Audit service shape and the newer
         * Advisor Audit payload without breaking existing reports.
         */
        $auditScore = $audit['audit_score']
            ?? $audit['overall_score']
            ?? null;

        $auditGrade = $audit['audit_grade']
            ?? match (true) {
                $auditScore === null => '—',
                $auditScore >= 90 => 'A',
                $auditScore >= 80 => 'B',
                $auditScore >= 70 => 'C',
                $auditScore >= 60 => 'D',
                default => 'F',
            };

        $auditLabel = $audit['audit_label']
            ?? $audit['overall_label']
            ?? 'Building advisor audit';

        $portfolioValue = (float) (
            $audit['portfolio_value']
            ?? data_get(
                $audit,
                'raw_analytics.cost.data.cost_analytics.portfolio_value',
                $accounts->sum('current_value')
            )
            ?? 0
        );

        $annualCost = (float) (
            $audit['annual_cost']
            ?? data_get(
                $audit,
                'categories.cost.metrics.annual_cost',
                0
            )
        );

        $potentialSavings = (float) (
            $audit['potential_savings']
            ?? data_get(
                $audit,
                'categories.cost.metrics.potential_savings',
                0
            )
        );

        $categoryScores = $audit['category_scores']
            ?? $audit['categories']
            ?? [];

        $executiveHeadline = data_get(
            $audit,
            'executive_summary.headline',
            'Your portfolio review is complete.'
        );

        $executiveSummary = data_get(
            $audit,
            'executive_summary.summary',
            'Helmio reviewed the available portfolio data across cost, performance, risk, diversification, trading, cash, and tax efficiency.'
        );

        $criticalFindings = collect($findings)
            ->filter(
                fn ($finding): bool =>
                    data_get($finding, 'severity') === 'critical'
            )
            ->values();

        $importantFindings = collect($findings)
            ->filter(
                fn ($finding): bool =>
                    in_array(
                        data_get($finding, 'severity'),
                        ['high', 'medium', 'moderate'],
                        true
                    )
            )
            ->values();

        $opportunityFindings = collect($findings)
            ->filter(
                fn ($finding): bool =>
                    in_array(
                        data_get($finding, 'severity'),
                        ['positive', 'information', 'informational'],
                        true
                    )
            )
            ->values();

        $activeFindings = collect($findings)
            ->filter(
                fn ($finding): bool =>
                    ! in_array(
                        data_get($finding, 'status'),
                        ['dismissed', 'resolved'],
                        true
                    )
            );

        $issueCount = (int) (
            $audit['issue_count']
            ?? (
                $criticalFindings->count()
                + $importantFindings->count()
            )
        );

        $questions = collect($activeFindings)
            ->map(function ($finding): ?string {
                $category = data_get($finding, 'category');

                return match ($category) {
                    'cost' =>
                        'Can you explain every advisory, fund, transaction, and account fee I am paying?',

                    'performance' =>
                        'Why has this portfolio performed differently from the selected benchmark?',

                    'risk' =>
                        'Does the current level of portfolio risk match my goals and documented risk tolerance?',

                    'diversification' =>
                        'What is the rationale for the portfolio’s largest positions and concentrated exposures?',

                    'trading' =>
                        'What client benefit did the recent trading activity provide after fees and taxes?',

                    'cash' =>
                        'What is the intended purpose of the current cash allocation?',

                    'tax' =>
                        'What tax-management opportunities or risks should we review before year-end?',

                    default =>
                        null,
                };
            })
            ->filter()
            ->unique()
            ->take(6)
            ->values();

        $formulaVersion = $audit['formula_version']
            ?? 'Not available';

        $helmFormulaVersion = data_get(
            $audit,
            'helm_score.formula_version',
            data_get(
                $audit,
                'scoring_formula_version',
                'Not available'
            )
        );
    @endphp

    <div class="cover">
        <p class="brand">
            HELMIO
        </p>

        <p class="brand-subtitle">
            Investment oversight
        </p>

        <h1 class="cover-title">
            Advisor Audit Report
        </h1>

        <p class="cover-description">
            A plain-English review of portfolio costs, performance,
            risk, diversification, trading behavior, cash management,
            and tax efficiency.
        </p>

        <table class="cover-score-table">
            <tr>
                <td>
                    <div class="cover-metric">
                        <p class="cover-label">
                            Advisor grade
                        </p>

                        <p class="cover-value">
                            {{ $auditGrade }}
                        </p>

                        <p class="cover-note">
                            {{ $auditLabel }}
                        </p>
                    </div>
                </td>

                <td>
                    <div class="cover-metric">
                        <p class="cover-label">
                            Advisor Audit score
                        </p>

                        <p class="cover-value">
                            {{ $auditScore ?? '—' }}
                        </p>

                        <p class="cover-note">
                            {{ $auditScore !== null
                                ? 'Out of 100'
                                : 'More data required' }}
                        </p>
                    </div>
                </td>

                <td>
                    <div class="cover-metric">
                        <p class="cover-label">
                            Portfolio value
                        </p>

                        <p class="cover-value-small">
                            ${{ number_format(
                                $portfolioValue,
                                0
                            ) }}
                        </p>

                        <p class="cover-note">
                            Across
                            {{ number_format($accounts->count()) }}
                            account(s)
                        </p>
                    </div>
                </td>
            </tr>
        </table>

        <table class="cover-footer">
            <tr>
                <td>
                    <p class="cover-footer-label">
                        Prepared for
                    </p>

                    <p class="cover-footer-value">
                        {{ $user->name }}
                    </p>
                </td>

                <td class="cover-footer-right">
                    <p class="cover-footer-label">
                        Generated
                    </p>

                    <p class="cover-footer-value">
                        {{ $generatedAt->format('F j, Y') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <table class="report-header">
        <tr>
            <td class="report-header-brand">
                HELMIO
            </td>

            <td class="report-header-title">
                Advisor Audit Report
            </td>
        </tr>
    </table>

    <div class="section-heading">
        <p class="section-eyebrow">
            Executive summary
        </p>

        <h2 class="section-title">
            Overall assessment
        </h2>
    </div>

    <div class="hero-summary">
        <table>
            <tr>
                <td class="hero-score">
                    <p class="cover-label">
                        Advisor Audit score
                    </p>

                    <p class="hero-score-value">
                        {{ $auditScore ?? '—' }}
                    </p>

                    <p class="hero-score-label">
                        {{ $auditGrade }}
                        ·
                        {{ $auditLabel }}
                    </p>
                </td>

                <td class="hero-narrative">
                    <p class="hero-headline">
                        {{ $executiveHeadline }}
                    </p>

                    <p class="hero-copy">
                        {{ $executiveSummary }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <table class="metric-table">
        <tr>
            <td>
                <div class="metric-card">
                    <p class="metric-label">
                        Portfolio value
                    </p>

                    <p class="metric-value">
                        ${{ number_format(
                            $portfolioValue,
                            0
                        ) }}
                    </p>
                </div>
            </td>

            <td>
                <div class="metric-card">
                    <p class="metric-label">
                        Estimated annual cost
                    </p>

                    <p class="metric-value">
                        ${{ number_format(
                            $annualCost,
                            0
                        ) }}
                    </p>
                </div>
            </td>

            <td>
                <div class="metric-card">
                    <p class="metric-label">
                        Potential annual savings
                    </p>

                    <p class="metric-value">
                        ${{ number_format(
                            $potentialSavings,
                            0
                        ) }}
                    </p>
                </div>
            </td>

            <td>
                <div class="metric-card">
                    <p class="metric-label">
                        Review items
                    </p>

                    <p class="metric-value">
                        {{ number_format($issueCount) }}
                    </p>
                </div>
            </td>
        </tr>
    </table>

    <table class="metric-table">
        <tr>
            <td>
                <div class="metric-card metric-card-red">
                    <p class="metric-label">
                        Critical findings
                    </p>

                    <p class="metric-value">
                        {{ number_format(
                            $criticalFindings->count()
                        ) }}
                    </p>
                </div>
            </td>

            <td>
                <div class="metric-card metric-card-amber">
                    <p class="metric-label">
                        Important findings
                    </p>

                    <p class="metric-value">
                        {{ number_format(
                            $importantFindings->count()
                        ) }}
                    </p>
                </div>
            </td>

            <td>
                <div class="metric-card metric-card-green">
                    <p class="metric-label">
                        Opportunities
                    </p>

                    <p class="metric-value">
                        {{ number_format(
                            $opportunityFindings->count()
                        ) }}
                    </p>
                </div>
            </td>

            <td>
                <div class="metric-card">
                    <p class="metric-label">
                        Accounts reviewed
                    </p>

                    <p class="metric-value">
                        {{ number_format(
                            $accounts->count()
                        ) }}
                    </p>
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-heading">
            <p class="section-eyebrow">
                Portfolio health
            </p>

            <h2 class="section-title">
                Category score breakdown
            </h2>

            <p class="section-copy">
                Each score represents one component of the Advisor Audit.
                A missing score means the available data was not sufficient
                for a reliable calculation.
            </p>
        </div>

        @forelse ($categoryScores as $key => $category)
            @php
                $categoryScore = is_array($category)
                    ? ($category['score'] ?? null)
                    : (
                        is_numeric($category)
                            ? (int) $category
                            : null
                    );

                $categoryLabel = is_array($category)
                    ? ($category['label'] ?? 'Not available')
                    : (
                        $categoryScore !== null
                            ? match (true) {
                                $categoryScore >= 90 => 'Excellent',
                                $categoryScore >= 80 => 'Very good',
                                $categoryScore >= 70 => 'Good',
                                $categoryScore >= 60 => 'Fair',
                                $categoryScore >= 40 => 'Needs attention',
                                default => 'Action recommended',
                            }
                            : 'Not available'
                    );
            @endphp

            <table class="score-row">
                <tr>
                    <td class="score-name">
                        {{ str($key)
                            ->replace('_', ' ')
                            ->title() }}
                    </td>

                    <td class="score-track-cell">
                        <div class="score-track">
                            <div
                                class="score-fill"
                                style="width: {{ min(
                                    100,
                                    max(
                                        0,
                                        $categoryScore ?? 0
                                    )
                                ) }}%;"
                            ></div>
                        </div>
                    </td>

                    <td class="score-number">
                        {{ $categoryScore ?? '—' }}
                    </td>

                    <td class="score-assessment">
                        {{ $categoryLabel }}
                    </td>
                </tr>
            </table>
        @empty
            <div class="disclosure">
                Category scores are not available for this report.
            </div>
        @endforelse
    </div>

    <div class="page-break"></div>

    <table class="report-header">
        <tr>
            <td class="report-header-brand">
                HELMIO
            </td>

            <td class="report-header-title">
                Priority findings
            </td>
        </tr>
    </table>

    <div class="section-heading">
        <p class="section-eyebrow">
            Priority findings
        </p>

        <h2 class="section-title">
            What deserves attention
        </h2>

        <p class="section-copy">
            Findings are grouped by urgency. Financial impact is shown
            when the underlying analytics provide a supportable estimate.
        </p>
    </div>

    @if ($criticalFindings->isNotEmpty())
        <div class="section avoid-break">
            <h3 class="section-title" style="font-size: 13px;">
                Critical
            </h3>

            @foreach ($criticalFindings as $finding)
                @php
                    $impact = data_get(
                        $finding,
                        'metadata.financial_impact'
                    );
                @endphp

                <div class="finding finding-critical">
                    <table class="finding-header">
                        <tr>
                            <td class="finding-main">
                                <span class="badge badge-red">
                                    Critical
                                </span>

                                <span class="badge">
                                    {{ data_get(
                                        $finding,
                                        'category',
                                        'Audit'
                                    ) }}
                                </span>

                                <p class="finding-title">
                                    {{ data_get(
                                        $finding,
                                        'title',
                                        'Advisor audit finding'
                                    ) }}
                                </p>

                                <p class="finding-copy">
                                    {{ data_get(
                                        $finding,
                                        'description',
                                        data_get(
                                            $finding,
                                            'message',
                                            ''
                                        )
                                    ) }}
                                </p>
                            </td>

                            <td class="finding-impact">
                                @if (is_numeric($impact))
                                    <p class="finding-impact-label">
                                        Estimated impact
                                    </p>

                                    <p class="finding-impact-value">
                                        ${{ number_format(
                                            (float) $impact,
                                            0
                                        ) }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if (data_get($finding, 'recommendation'))
                        <div class="recommendation">
                            <strong>Recommended action:</strong>
                            {{ data_get(
                                $finding,
                                'recommendation'
                            ) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($importantFindings->isNotEmpty())
        <div class="section">
            <h3 class="section-title" style="font-size: 13px;">
                Important
            </h3>

            @foreach ($importantFindings as $finding)
                @php
                    $impact = data_get(
                        $finding,
                        'metadata.financial_impact'
                    );
                @endphp

                <div class="finding finding-important">
                    <table class="finding-header">
                        <tr>
                            <td class="finding-main">
                                <span class="badge badge-amber">
                                    Important
                                </span>

                                <span class="badge">
                                    {{ data_get(
                                        $finding,
                                        'category',
                                        'Audit'
                                    ) }}
                                </span>

                                <p class="finding-title">
                                    {{ data_get(
                                        $finding,
                                        'title',
                                        'Advisor audit finding'
                                    ) }}
                                </p>

                                <p class="finding-copy">
                                    {{ data_get(
                                        $finding,
                                        'description',
                                        data_get(
                                            $finding,
                                            'message',
                                            ''
                                        )
                                    ) }}
                                </p>
                            </td>

                            <td class="finding-impact">
                                @if (is_numeric($impact))
                                    <p class="finding-impact-label">
                                        Estimated impact
                                    </p>

                                    <p class="finding-impact-value">
                                        ${{ number_format(
                                            (float) $impact,
                                            0
                                        ) }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if (data_get($finding, 'recommendation'))
                        <div class="recommendation">
                            <strong>Recommended action:</strong>
                            {{ data_get(
                                $finding,
                                'recommendation'
                            ) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($opportunityFindings->isNotEmpty())
        <div class="section">
            <h3 class="section-title" style="font-size: 13px;">
                Opportunities
            </h3>

            @foreach ($opportunityFindings as $finding)
                @php
                    $impact = data_get(
                        $finding,
                        'metadata.financial_impact'
                    );
                @endphp

                <div class="finding finding-opportunity">
                    <table class="finding-header">
                        <tr>
                            <td class="finding-main">
                                <span class="badge badge-green">
                                    Opportunity
                                </span>

                                <span class="badge">
                                    {{ data_get(
                                        $finding,
                                        'category',
                                        'Audit'
                                    ) }}
                                </span>

                                <p class="finding-title">
                                    {{ data_get(
                                        $finding,
                                        'title',
                                        'Portfolio opportunity'
                                    ) }}
                                </p>

                                <p class="finding-copy">
                                    {{ data_get(
                                        $finding,
                                        'description',
                                        data_get(
                                            $finding,
                                            'message',
                                            ''
                                        )
                                    ) }}
                                </p>
                            </td>

                            <td class="finding-impact">
                                @if (is_numeric($impact))
                                    <p class="finding-impact-label">
                                        Estimated impact
                                    </p>

                                    <p class="finding-impact-value">
                                        ${{ number_format(
                                            (float) $impact,
                                            0
                                        ) }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if (data_get($finding, 'recommendation'))
                        <div class="recommendation">
                            <strong>Recommended action:</strong>
                            {{ data_get(
                                $finding,
                                'recommendation'
                            ) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if (
        $criticalFindings->isEmpty()
        && $importantFindings->isEmpty()
        && $opportunityFindings->isEmpty()
    )
        <div class="disclosure">
            No persisted Advisor Audit findings were available for this report.
        </div>
    @endif

    <div class="page-break"></div>

    <table class="report-header">
        <tr>
            <td class="report-header-brand">
                HELMIO
            </td>

            <td class="report-header-title">
                Advisor discussion guide
            </td>
        </tr>
    </table>

    <div class="section-heading">
        <p class="section-eyebrow">
            Advisor discussion guide
        </p>

        <h2 class="section-title">
            Questions to ask your advisor
        </h2>

        <p class="section-copy">
            Use these questions to better understand the reasoning,
            costs, risks, and expected benefits behind the portfolio.
        </p>
    </div>

    <div class="questions-box">
        <p class="questions-title">
            Suggested questions
        </p>

        @forelse ($questions as $question)
            <div class="question-item">
                {{ $loop->iteration }}.
                {{ $question }}
            </div>
        @empty
            <div class="question-item">
                1. What are the most important strengths and weaknesses
                in my current portfolio?
            </div>

            <div class="question-item">
                2. Which portfolio changes would have the greatest
                long-term financial impact?
            </div>

            <div class="question-item">
                3. What data or documents would improve the completeness
                of this review?
            </div>
        @endforelse
    </div>

    <div class="section">
        <div class="section-heading">
            <p class="section-eyebrow">
                Portfolio snapshot
            </p>

            <h2 class="section-title">
                Accounts reviewed
            </h2>
        </div>

        <table class="account-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Institution</th>
                    <th>Type</th>
                    <th class="number">Value</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($accounts as $account)
                    <tr>
                        <td>
                            {{ $account->name }}
                        </td>

                        <td>
                            {{ $account->institution?->name
                                ?? 'Manual account' }}
                        </td>

                        <td>
                            {{ str(
                                $account->account_type
                                ?? 'investment'
                            )
                                ->replace('_', ' ')
                                ->title() }}
                        </td>

                        <td class="number">
                            ${{ number_format(
                                (float) (
                                    $account->current_value
                                    ?? 0
                                ),
                                2
                            ) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            No investment accounts were available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-heading">
            <p class="section-eyebrow">
                Methodology
            </p>

            <h2 class="section-title">
                Methodology and limitations
            </h2>
        </div>

        <div class="disclosure">
            <p>
                Helmio calculates category scores using deterministic,
                versioned formulas applied to the portfolio information
                entered or imported into the application.
            </p>

            <p>
                This report identifies patterns that may deserve review.
                It does not determine whether advice was suitable,
                authorized, negligent, conflicted, or legally improper,
                and it does not provide investment, tax, accounting, or
                legal advice.
            </p>

            <p>
                Complete analysis may require brokerage statements,
                advisory agreements, adjusted tax basis, investor
                objectives, and review by qualified professionals.
            </p>

            <p>
                Estimated financial impact and potential savings are
                directional estimates based on the available data and
                should not be treated as guaranteed outcomes.
            </p>
        </div>
    </div>

    <div class="footer">
        Advisor Audit version:
        {{ $formulaVersion }}

        · Scoring version:
        {{ $helmFormulaVersion }}

        · Generated:
        {{ $generatedAt->format('Y-m-d H:i') }}
    </div>
</body>
</html>