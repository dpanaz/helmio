@php
    $toneClasses = match ($tone ?? 'important') {
        'critical' =>
            'border-red-200 bg-white',

        'opportunity' =>
            'border-emerald-200 bg-emerald-50',

        default =>
            'border-amber-200 bg-amber-50',
    };

    $badgeClasses = match ($tone ?? 'important') {
        'critical' =>
            'bg-red-100 text-red-800',

        'opportunity' =>
            'bg-emerald-100 text-emerald-800',

        default =>
            'bg-amber-100 text-amber-800',
    };
@endphp

<article class="rounded-xl border p-5 {{ $toneClasses }}">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ $finding['category_label'] }}
                </span>

                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClasses }}">
                    {{ $finding['severity_label'] }}
                </span>

                @if ($finding['status'] === \App\Models\AuditFinding::STATUS_REVIEWED)
                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800">
                        Reviewed
                    </span>
                @endif
            </div>

            <h4 class="mt-3 text-base font-semibold text-slate-950">
                {{ $finding['title'] }}
            </h4>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                {{ $finding['description'] }}
            </p>

            @if (! empty($finding['recommendation']))
                <div class="mt-4 rounded-lg bg-white/70 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Recommended action
                    </p>

                    <p class="mt-1 text-sm leading-6 text-slate-700">
                        {{ $finding['recommendation'] }}
                    </p>
                </div>
            @endif
        </div>

        <div class="shrink-0 sm:w-44">
            @if ($finding['financial_impact'] !== null)
                <div class="rounded-lg bg-white p-3 text-right ring-1 ring-slate-200">
                    <p class="text-xs text-slate-500">
                        Estimated impact
                    </p>

                    <p class="mt-1 text-lg font-semibold text-slate-950">
                        {{ money(
                            $finding['financial_impact'],
                            0
                        ) }}
                    </p>
                </div>
            @endif

            <a
                href="{{ route($finding['route_name']) }}"
                class="mt-3 inline-flex w-full items-center justify-center rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
                Review Details
            </a>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/70 pt-4">
        <p class="text-xs text-slate-500">
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
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
            >
                Mark Reviewed
            </button>

            <button
                type="submit"
                name="status"
                value="{{ \App\Models\AuditFinding::STATUS_DISMISSED }}"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50"
            >
                Dismiss
            </button>
        </form>
    </div>
</article>