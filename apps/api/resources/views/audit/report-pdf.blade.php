<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Helmio Advisor Audit</title>

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
            line-height: 1.45;
        }

        h1,
        h2,
        h3,
        h4,
        p {
            margin: 0;
        }

        .header {
            padding: 24px;
            color: #ffffff;
            background: #020617;
            border-radius: 12px;
        }

        .header-table,
        .summary-table,
        .score-table,
        .account-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-grade {
            width: 55%;
            vertical-align: top;
        }

        .header-meta {
            width: 45%;
            text-align: right;
            vertical-align: top;
        }

        .eyebrow {
            margin-bottom: 8px;
            color: #93c5fd;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .grade {
            font-size: 52px;
            font-weight: bold;
            line-height: 1;
        }

        .score {
            margin-top: 6px;
            color: #cbd5e1;
            font-size: 15px;
            font-weight: bold;
        }

        .muted-light {
            margin-top: 5px;
            color: #94a3b8;
        }

        .section {
            margin-top: 20px;
        }

        .section-title {
            margin-bottom: 10px;
            font-size: 15px;
            font-weight: bold;
        }

        .summary-table td {
            width: 25%;
            padding: 10px 7px;
            vertical-align: top;
        }

        .summary-card {
            min-height: 68px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .label {
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .value {
            margin-top: 6px;
            font-size: 17px;
            font-weight: bold;
        }

        .account-table,
        .score-table {
            border: 1px solid #e2e8f0;
        }

        .account-table th,
        .account-table td,
        .score-table th,
        .score-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .account-table th,
        .score-table th {
            color: #475569;
            background: #f8fafc;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .number {
            text-align: right !important;
        }

        .finding {
            margin-bottom: 12px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .finding-title {
            margin-top: 8px;
            font-size: 12px;
            font-weight: bold;
        }

        .finding-copy {
            margin-top: 7px;
            color: #475569;
        }

        .recommendation {
            margin-top: 9px;
            padding: 9px;
            color: #334155;
            background: #f8fafc;
            border-radius: 6px;
        }

        .badge {
            display: inline-block;
            margin-right: 5px;
            padding: 3px 7px;
            color: #334155;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
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
            font-size: 8px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-grade">
                    <p class="eyebrow">
                        Helmio Advisor Audit
                    </p>

                    <p class="grade">
                        {{ $audit['audit_grade'] }}
                    </p>

                    <p class="score">
                        {{ $audit['audit_score'] ?? '—' }}
                        @if ($audit['audit_score'] !== null)
                            / 100
                        @endif
                        · {{ $audit['audit_label'] }}
                    </p>
                </td>

                <td class="header-meta">
                    <p>
                        Prepared for
                    </p>

                    <p style="margin-top: 5px; font-size: 14px; font-weight: bold;">
                        {{ $user->name }}
                    </p>

                    <p class="muted-light">
                        {{ $generatedAt->format('F j, Y') }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-card">
                        <p class="label">Portfolio value</p>
                        <p class="value">
                            ${{ number_format(
                                $audit['portfolio_value'],
                                2
                            ) }}
                        </p>
                    </div>
                </td>

                <td>
                    <div class="summary-card">
                        <p class="label">Annual cost</p>
                        <p class="value">
                            ${{ number_format(
                                $audit['annual_cost'],
                                2
                            ) }}
                        </p>
                    </div>
                </td>

                <td>
                    <div class="summary-card">
                        <p class="label">Potential savings</p>
                        <p class="value">
                            ${{ number_format(
                                $audit['potential_savings'],
                                2
                            ) }}
                        </p>
                    </div>
                </td>

                <td>
                    <div class="summary-card">
                        <p class="label">Review items</p>
                        <p class="value">
                            {{ $audit['issue_count'] }}
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">
            Accounts reviewed
        </h2>

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
                @foreach ($accounts as $account)
                    <tr>
                        <td>{{ $account->name }}</td>

                        <td>
                            {{ $account->institution?->name
                                ?? 'Manual account' }}
                        </td>

                        <td>
                            {{ str($account->account_type)
                                ->replace('_', ' ')
                                ->title() }}
                        </td>

                        <td class="number">
                            ${{ number_format(
                                $account->current_value,
                                2
                            ) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">
            Category scores
        </h2>

        <table class="score-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Assessment</th>
                    <th class="number">Score</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($audit['category_scores'] as $key => $category)
                    <tr>
                        <td>
                            {{ str($key)
                                ->replace('_', ' ')
                                ->title() }}
                        </td>

                        <td>
                            {{ $category['label'] }}
                        </td>

                        <td class="number">
                            {{ $category['score'] ?? '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <h2 class="section-title">
            Audit findings
        </h2>

        @forelse ($findings as $finding)
            <div class="finding">
                <span class="badge">
                    {{ $finding->severity }}
                </span>

                <span class="badge">
                    {{ $finding->status }}
                </span>

                <p class="finding-title">
                    {{ $finding->title }}
                </p>

                <p class="finding-copy">
                    {{ $finding->description }}
                </p>

                @if ($finding->recommendation)
                    <div class="recommendation">
                        <strong>Suggested review:</strong>
                        {{ $finding->recommendation }}
                    </div>
                @endif

                @if ($finding->review_notes)
                    <div class="recommendation">
                        <strong>Review notes:</strong>
                        {{ $finding->review_notes }}
                    </div>
                @endif
            </div>
        @empty
            <p>No audit findings are available.</p>
        @endforelse
    </div>

    <div class="section">
        <h2 class="section-title">
            Methodology and limitations
        </h2>

        <div class="disclosure">
            <p>
                Helmio calculates category scores using deterministic,
                versioned formulas applied to the portfolio information entered
                or imported into the application.
            </p>

            <p>
                This report identifies patterns that may deserve review. It
                does not determine whether advice was suitable, authorized,
                negligent, conflicted or legally improper and does not provide
                investment, tax, accounting or legal advice.
            </p>

            <p>
                Complete analysis may require brokerage statements, advisory
                agreements, adjusted tax basis, investor objectives and review
                by qualified professionals.
            </p>
        </div>
    </div>

    <div class="footer">
        Advisor Audit version:
        {{ $audit['formula_version'] }}

        · Helm Score version:
        {{ $audit['helm_score']['formula_version'] }}

        · Generated:
        {{ $generatedAt->format('Y-m-d H:i') }}
    </div>
</body>
</html>