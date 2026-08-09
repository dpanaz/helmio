@php
    $toneClasses = match (
        $tone ?? 'important'
    ) {
        'critical' =>
            'border-red-500/25 bg-red-500/[0.06]',

        'opportunity' =>
            'border-emerald-500/20 bg-emerald-500/[0.05]',

        default =>
            'border-amber-500/20 bg-amber-500/[0.05]',
    };

    $badgeClasses = match (
        $tone ?? 'important'
    ) {
        'critical' =>
            'border-red-500/20 bg-red-500/10 text-red-300',

        'opportunity' =>
            'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',

        default =>
            'border-amber-500/20 bg-amber-500/10 text-amber-300',
    };
@endphp

<article
    class="rounded-2xl border p-5 {{ $toneClasses }}"
>
    <div
        class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
    >
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                >
                    {{ $finding['category_label'] }}
                </span>

                <span
                    class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClasses }}"
                >
                    {{ $finding['severity_label'] }}
                </span>

                @if (
                    $finding['status']
                    === \App\Models\AuditFinding::STATUS_REVIEWED
                )
                    <span
                        class="rounded-full border border-blue-500/20 bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-300"
                    >
                        Reviewed
                    </span>
                @endif
            </div>

            <h4
                class="mt-3 text-base font-semibold text-white"
            >
                {{ $finding['title'] }}
            </h4>

            <p
                class="mt-2 text-sm leading-6 text-slate-400"
            >
                {{ $finding['description'] }}
            </p>

            @if (! empty($finding['recommendation']))
                <div
                    class="mt-4 rounded-xl border border-slate-800 bg-slate-950/70 p-4"
                >
                    <p
                        class="text-xs font-semibold uppercase tracking-wide text-blue-400"
                    >
                        Recommended action
                    </p>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-300"
                    >
                        {{ $finding['recommendation'] }}
                    </p>
                </div>
            @endif
        </div>

        <div class="shrink-0 sm:w-44">
            @if ($finding['financial_impact'] !== null)
                <div
                    class="rounded-xl border border-slate-800 bg-slate-950 p-4 text-right"
                >
                    <p class="text-xs text-slate-600">
                        Estimated impact
                    </p>

                    <p
                        class="mt-1 text-lg font-semibold text-white"
                    >
                        {{ money(
                            $finding['financial_impact'],
                            0
                        ) }}
                    </p>
                </div>
            @endif

            <a
                href="{{ route($finding['route_name']) }}"
                class="mt-3 inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
            >
                Review Details
            </a>
        </div>
    </div>

    <div
        class="mt-5 flex flex-col gap-4 border-t border-slate-800 pt-4 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-xs text-slate-600">
            Last detected
            {{ $finding['last_detected_at']
                ? \Carbon\Carbon::parse(
                    $finding['last_detected_at']
                )->diffForHumans()
                : 'recently' }}
        </p>

        <form
            method="POST"
            action="{{ route(
                'audit-findings.update',
                $finding['id']
            ) }}"
            class="flex flex-wrap gap-2"
        >
            @csrf
            @method('PATCH')

            <button
                type="submit"
                name="status"
                value="{{ \App\Models\AuditFinding::STATUS_REVIEWED }}"
                class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-blue-500 hover:text-white"
            >
                Mark Reviewed
            </button>

            <button
                type="submit"
                name="status"
                value="{{ \App\Models\AuditFinding::STATUS_DISMISSED }}"
                class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs font-semibold text-slate-500 transition hover:border-red-500/50 hover:text-red-300"
            >
                Dismiss
            </button>
        </form>
    </div>
</article>