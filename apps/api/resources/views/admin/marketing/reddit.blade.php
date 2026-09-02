<x-app-layout>
<style>
    .reddit-dashboard {
        --navy: #0b1736;
        --blue: #2563eb;
        --cyan: #06b6d4;
        --green: #10b981;
        --orange: #f97316;
        --muted: #64748b;
        --line: #e2e8f0;
        --surface: #ffffff;
        max-width: 1440px;
        margin: 0 auto;
        padding: 32px 28px 56px;
        color: #172033;
    }

    .reddit-dashboard * {
        box-sizing: border-box;
    }

    .reddit-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 28px;
    }

    .reddit-eyebrow {
        margin: 0 0 8px;
        color: var(--orange);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .reddit-title {
        margin: 0;
        color: var(--navy);
        font-size: clamp(28px, 4vw, 42px);
        line-height: 1.08;
        letter-spacing: -.035em;
    }

    .reddit-subtitle {
        max-width: 680px;
        margin: 10px 0 0;
        color: var(--muted);
        font-size: 15px;
        line-height: 1.6;
    }

    .period-filter {
        display: inline-flex;
        gap: 5px;
        padding: 5px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: #f8fafc;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
    }

    .period-filter a {
        padding: 9px 12px;
        border-radius: 8px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .period-filter a.active {
        color: #fff;
        background: var(--navy);
        box-shadow: 0 5px 14px rgba(11, 23, 54, .18);
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .metric-card,
    .panel {
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--surface);
        box-shadow: 0 12px 35px rgba(15, 23, 42, .055);
    }

    .metric-card {
        min-height: 128px;
        padding: 19px;
        position: relative;
        overflow: hidden;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        top: -35px;
        right: -35px;
        width: 85px;
        height: 85px;
        border-radius: 50%;
        background: var(--accent, var(--blue));
        opacity: .08;
    }

    .metric-label {
        display: block;
        margin-bottom: 13px;
        color: var(--muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .075em;
        text-transform: uppercase;
    }

    .metric-value {
        display: block;
        color: var(--navy);
        font-size: 30px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -.035em;
    }

    .metric-detail {
        display: block;
        margin-top: 10px;
        color: var(--muted);
        font-size: 12px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(310px, .6fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .panel {
        padding: 24px;
    }

    .panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
    }

    .panel-title {
        margin: 0;
        color: var(--navy);
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.015em;
    }

    .panel-description {
        margin: 5px 0 0;
        color: var(--muted);
        font-size: 13px;
    }

    .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border-radius: 999px;
        color: #047857;
        background: #ecfdf5;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .live-badge::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--green);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
    }

    .funnel-list {
        display: grid;
        gap: 14px;
    }

    .funnel-row {
        display: grid;
        grid-template-columns: 145px minmax(110px, 1fr) 62px;
        align-items: center;
        gap: 14px;
    }

    .funnel-name {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .funnel-track {
        height: 12px;
        overflow: hidden;
        border-radius: 999px;
        background: #edf2f7;
    }

    .funnel-fill {
        min-width: 3px;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--start), var(--end));
    }

    .funnel-count {
        color: var(--navy);
        font-size: 14px;
        font-weight: 800;
        text-align: right;
    }

    .rate-list {
        display: grid;
        gap: 14px;
    }

    .rate-card {
        padding: 15px 16px;
        border: 1px solid #e8edf4;
        border-radius: 12px;
        background: #f8fafc;
    }

    .rate-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .rate-name {
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }

    .rate-value {
        color: var(--navy);
        font-size: 20px;
        font-weight: 800;
    }

    .rate-track {
        height: 5px;
        margin-top: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .rate-fill {
        height: 100%;
        border-radius: inherit;
        background: var(--color, var(--blue));
    }

    .table-panel {
        padding: 0;
        overflow: hidden;
    }

    .table-panel .panel-header {
        padding: 24px 24px 0;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .campaign-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 950px;
    }

    .campaign-table th {
        padding: 12px 16px;
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
        color: var(--muted);
        background: #f8fafc;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .07em;
        text-align: right;
        text-transform: uppercase;
    }

    .campaign-table th:first-child,
    .campaign-table td:first-child {
        padding-left: 24px;
        text-align: left;
    }

    .campaign-table th:nth-child(2),
    .campaign-table td:nth-child(2) {
        text-align: left;
    }

    .campaign-table td {
        padding: 17px 16px;
        border-bottom: 1px solid #edf2f7;
        color: #334155;
        font-size: 13px;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    .campaign-table tbody tr:hover {
        background: #fbfdff;
    }

    .campaign-name {
        color: var(--navy);
        font-weight: 800;
    }

    .content-name {
        display: inline-block;
        padding: 5px 8px;
        border-radius: 7px;
        color: #475569;
        background: #f1f5f9;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 11px;
    }

    .rate-pill {
        display: inline-block;
        min-width: 52px;
        padding: 5px 7px;
        border-radius: 7px;
        color: #1d4ed8;
        background: #eff6ff;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
    }

    .empty-state {
        padding: 58px 24px;
        color: var(--muted);
        text-align: center;
    }

    .empty-state strong {
        display: block;
        margin-bottom: 7px;
        color: var(--navy);
        font-size: 16px;
    }

    @media (max-width: 1180px) {
        .metric-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 820px) {
        .reddit-dashboard {
            padding: 24px 16px 40px;
        }

        .reddit-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 620px) {
        .metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .period-filter {
            width: 100%;
        }

        .period-filter a {
            flex: 1;
            text-align: center;
        }

        .funnel-row {
            grid-template-columns: 108px minmax(80px, 1fr) 44px;
            gap: 9px;
        }

        .panel {
            padding: 19px;
        }
    }
</style>

@php
    $visitorBase = max(1, (int) $metrics['visitors']);
    $funnel = [
        [
            'name' => 'Visitors',
            'count' => $metrics['visitors'],
            'width' => 100,
            'start' => '#64748b',
            'end' => '#94a3b8',
        ],
        [
            'name' => 'Signups',
            'count' => $metrics['signups'],
            'width' => ($metrics['signups'] / $visitorBase) * 100,
            'start' => '#2563eb',
            'end' => '#3b82f6',
        ],
        [
            'name' => 'Accounts connected',
            'count' => $metrics['connected_accounts'],
            'width' => ($metrics['connected_accounts'] / $visitorBase) * 100,
            'start' => '#06b6d4',
            'end' => '#22d3ee',
        ],
        [
            'name' => 'Audits completed',
            'count' => $metrics['audits_completed'],
            'width' => ($metrics['audits_completed'] / $visitorBase) * 100,
            'start' => '#8b5cf6',
            'end' => '#a78bfa',
        ],
        [
            'name' => 'Paid subscribers',
            'count' => $metrics['paid_subscribers'],
            'width' => ($metrics['paid_subscribers'] / $visitorBase) * 100,
            'start' => '#10b981',
            'end' => '#34d399',
        ],
    ];
@endphp

<div class="reddit-dashboard">
    <header class="reddit-header">
        <div>
            <p class="reddit-eyebrow">Acquisition intelligence</p>
            <h1 class="reddit-title">Reddit Campaigns</h1>
            <p class="reddit-subtitle">
                Follow each Reddit visitor from the first click through signup,
                account connection, advisor audit, and paid subscription.
            </p>
        </div>

        <nav class="period-filter" aria-label="Reporting period">
            @foreach ([7, 30, 90, 365] as $period)
                <a
                    href="{{ route('admin.marketing.reddit', ['days' => $period]) }}"
                    class="{{ $days === $period ? 'active' : '' }}"
                >
                    {{ $period === 365 ? '1 year' : $period.' days' }}
                </a>
            @endforeach
        </nav>
    </header>

    <section class="metric-grid" aria-label="Reddit acquisition summary">
        <article class="metric-card" style="--accent: #64748b">
            <span class="metric-label">Visitors</span>
            <strong class="metric-value">{{ number_format($metrics['visitors']) }}</strong>
            <span class="metric-detail">Unique attributed visitors</span>
        </article>

        <article class="metric-card" style="--accent: #2563eb">
            <span class="metric-label">Signups</span>
            <strong class="metric-value">{{ number_format($metrics['signups']) }}</strong>
            <span class="metric-detail">{{ number_format($metrics['signup_rate'], 1) }}% of visitors</span>
        </article>

        <article class="metric-card" style="--accent: #06b6d4">
            <span class="metric-label">Connected</span>
            <strong class="metric-value">{{ number_format($metrics['connected_accounts']) }}</strong>
            <span class="metric-detail">{{ number_format($metrics['connection_rate'], 1) }}% of visitors</span>
        </article>

        <article class="metric-card" style="--accent: #8b5cf6">
            <span class="metric-label">Audits</span>
            <strong class="metric-value">{{ number_format($metrics['audits_completed']) }}</strong>
            <span class="metric-detail">Completed advisor audits</span>
        </article>

        <article class="metric-card" style="--accent: #10b981">
            <span class="metric-label">Subscribers</span>
            <strong class="metric-value">{{ number_format($metrics['paid_subscribers']) }}</strong>
            <span class="metric-detail">{{ number_format($metrics['paid_rate'], 1) }}% of visitors</span>
        </article>

        <article class="metric-card" style="--accent: #f97316">
            <span class="metric-label">Revenue</span>
            <strong class="metric-value">${{ number_format($metrics['revenue'], 2) }}</strong>
            <span class="metric-detail">{{ number_format($metrics['purchases']) }} paid invoice{{ $metrics['purchases'] === 1 ? '' : 's' }}</span>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Conversion funnel</h2>
                    <p class="panel-description">Unique users moving through the Helmio journey.</p>
                </div>
                <span class="live-badge">CAPI live</span>
            </div>

            <div class="funnel-list">
                @foreach ($funnel as $stage)
                    <div class="funnel-row">
                        <span class="funnel-name">{{ $stage['name'] }}</span>
                        <div class="funnel-track">
                            <div
                                class="funnel-fill"
                                style="
                                    width: {{ min(100, max(0, $stage['width'])) }}%;
                                    --start: {{ $stage['start'] }};
                                    --end: {{ $stage['end'] }};
                                "
                            ></div>
                        </div>
                        <strong class="funnel-count">{{ number_format($stage['count']) }}</strong>
                    </div>
                @endforeach
            </div>
        </article>

        <aside class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Conversion rates</h2>
                    <p class="panel-description">Measured from attributed visitors.</p>
                </div>
            </div>

            <div class="rate-list">
                @foreach ([
                    ['name' => 'Visitor to signup', 'value' => $metrics['signup_rate'], 'color' => '#2563eb'],
                    ['name' => 'Visitor to connected', 'value' => $metrics['connection_rate'], 'color' => '#06b6d4'],
                    ['name' => 'Visitor to paid', 'value' => $metrics['paid_rate'], 'color' => '#10b981'],
                ] as $rate)
                    <div class="rate-card">
                        <div class="rate-top">
                            <span class="rate-name">{{ $rate['name'] }}</span>
                            <strong class="rate-value">{{ number_format($rate['value'], 1) }}%</strong>
                        </div>
                        <div class="rate-track">
                            <div
                                class="rate-fill"
                                style="width: {{ min(100, $rate['value']) }}%; --color: {{ $rate['color'] }}"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>
    </section>

    <section class="panel table-panel">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Campaign performance</h2>
                <p class="panel-description">Compare campaigns and creative variants using your own conversion data.</p>
            </div>
        </div>

        @if ($campaigns->isEmpty())
            <div class="empty-state">
                <strong>No Reddit campaign traffic yet</strong>
                Visits will appear after someone lands on Helmio through a tagged Reddit URL.
            </div>
        @else
            <div class="table-wrap">
                <table class="campaign-table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Creative</th>
                            <th>Visitors</th>
                            <th>Signups</th>
                            <th>Connected</th>
                            <th>Paid</th>
                            <th>Signup rate</th>
                            <th>Paid rate</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaigns as $campaign)
                            <tr>
                                <td><span class="campaign-name">{{ $campaign['campaign'] }}</span></td>
                                <td><span class="content-name">{{ $campaign['content'] }}</span></td>
                                <td>{{ number_format($campaign['visitors']) }}</td>
                                <td>{{ number_format($campaign['signups']) }}</td>
                                <td>{{ number_format($campaign['connected_accounts']) }}</td>
                                <td>{{ number_format($campaign['paid_subscribers']) }}</td>
                                <td><span class="rate-pill">{{ number_format($campaign['signup_rate'], 1) }}%</span></td>
                                <td><span class="rate-pill">{{ number_format($campaign['paid_rate'], 1) }}%</span></td>
                                <td>${{ number_format($campaign['revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
</x-app-layout>