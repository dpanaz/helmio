<div class="min-w-0 w-full max-w-full overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:p-6">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-3">
                            <p class="text-sm font-medium text-gray-500">
                                Advisor Audit
                            </p>

                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $auditScoreClasses }}"
                            >
                                {{ $auditLabel }}
                            </span>

                            @if ($scoreChange !== null)
                                <span
                                    class="text-xs font-semibold
                                        {{ $scoreDirection === 'up'
                                            ? 'text-green-700'
                                            : ($scoreDirection === 'down'
                                                ? 'text-red-700'
                                                : 'text-gray-500') }}"
                                >
                                    @if ($scoreDirection === 'up')
                                        +{{ number_format((float) $scoreChange) }}
                                    @elseif ($scoreDirection === 'down')
                                        {{ number_format((float) $scoreChange) }}
                                    @else
                                        No change
                                    @endif
                                    from prior audit
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-end gap-3">
                            <p class="text-6xl font-bold tracking-tight text-gray-900">
                                {{ $auditScore ?? '—' }}
                            </p>

                            <p class="pb-2 text-lg font-medium text-gray-400">
                                / 100
                            </p>
                        </div>

                        <h3 class="mt-5 text-xl font-semibold text-gray-900">
                            {{ data_get(
                                $audit,
                                'executive_summary.headline',
                                'Building your advisor audit'
                            ) }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ data_get(
                                $audit,
                                'executive_summary.summary',
                                'Connect accounts and add complete portfolio history to generate a full advisor assessment.'
                            ) }}
                        </p>
                    </div>

                    <div class="w-full max-w-sm">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">
                                Data completeness
                            </span>

                            <span class="font-semibold text-gray-900">
                                {{ number_format($auditCompleteness * 100) }}%
                            </span>
                        </div>

                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-gray-900"
                                style="width: {{ min(
                                    100,
                                    max(0, $auditCompleteness * 100)
                                ) }}%"
                            ></div>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-red-50 p-3 text-center">
                                <p class="text-xl font-semibold text-red-900">
                                    {{ number_format((int) $criticalCount) }}
                                </p>

                                <p class="mt-1 text-xs text-red-700">
                                    Critical
                                </p>
                            </div>

                            <div class="rounded-lg bg-amber-50 p-3 text-center">
                                <p class="text-xl font-semibold text-amber-900">
                                    {{ number_format((int) $importantCount) }}
                                </p>

                                <p class="mt-1 text-xs text-amber-700">
                                    Important
                                </p>
                            </div>

                            <div class="rounded-lg bg-emerald-50 p-3 text-center">
                                <p class="text-xl font-semibold text-emerald-900">
                                    {{ number_format((int) $opportunityCount) }}
                                </p>

                                <p class="mt-1 text-xs text-emerald-700">
                                    Opportunities
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('advisor-audit.index') }}"
                            class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            View Full Advisor Audit
                        </a>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-700">
                            Top concern
                        </p>

                        @if ($topConcern)
                            <p class="mt-2 font-semibold text-red-900">
                                {{ $topConcern['title'] ?? 'Advisor audit finding' }}
                            </p>

                            <p class="mt-1 text-sm leading-6 text-red-800">
                                {{ $topConcern['message'] ?? '' }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-red-800">
                                No major concerns were detected in the available data.
                            </p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                            Top opportunity
                        </p>

                        @if ($topOpportunity)
                            <p class="mt-2 font-semibold text-emerald-900">
                                {{ $topOpportunity['title'] ?? 'Portfolio opportunity' }}
                            </p>

                            <p class="mt-1 text-sm leading-6 text-emerald-800">
                                {{ $topOpportunity['message'] ?? '' }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-emerald-800">
                                No major opportunities were identified yet.
                            </p>
                        @endif
                    </div>
                </div>
            </div>