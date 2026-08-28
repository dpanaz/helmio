<?php

namespace App\Http\Controllers\Analytics;

use App\Data\Simulation\PortfolioChangeData;
use App\Http\Controllers\Controller;
use App\Models\WhatIfScenario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WhatIfScenarioController extends Controller
{
    public function index(
        Request $request,
    ): JsonResponse {
        $scenarios = WhatIfScenario::query()
            ->where(
                'user_id',
                $request->user()->id
            )
            ->latest('updated_at')
            ->get()
            ->map(
                fn (WhatIfScenario $scenario) =>
                    $this->scenarioPayload(
                        $scenario
                    )
            )
            ->values();

        return response()->json([
            'success' => true,
            'scenarios' => $scenarios,
        ]);
    }

    public function show(
        Request $request,
        WhatIfScenario $scenario,
    ): JsonResponse {
        $this->authorizeScenario(
            $request,
            $scenario
        );

        return response()->json([
            'success' => true,
            'scenario' =>
                $this->scenarioPayload(
                    $scenario
                ),
        ]);
    }

    public function store(
        Request $request,
    ): JsonResponse {
        $validated =
            $this->validateScenario(
                $request
            );

        $scenario =
            WhatIfScenario::query()
                ->create([
                    'user_id' =>
                        $request->user()->id,

                    'name' =>
                        trim(
                            $validated['name']
                        ),

                    'description' =>
                        $this->nullableString(
                            $validated[
                                'description'
                            ] ?? null
                        ),

                    'changes' =>
                        $this->normalizeChanges(
                            $validated['changes']
                        ),
                ]);

        return response()->json([
            'success' => true,
            'message' => 'Scenario saved.',
            'scenario' =>
                $this->scenarioPayload(
                    $scenario
                ),
        ], 201);
    }

    public function update(
        Request $request,
        WhatIfScenario $scenario,
    ): JsonResponse {
        $this->authorizeScenario(
            $request,
            $scenario
        );

        $validated =
            $this->validateScenario(
                $request
            );

        $scenario->update([
            'name' =>
                trim(
                    $validated['name']
                ),

            'description' =>
                $this->nullableString(
                    $validated[
                        'description'
                    ] ?? null
                ),

            'changes' =>
                $this->normalizeChanges(
                    $validated['changes']
                ),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scenario updated.',
            'scenario' =>
                $this->scenarioPayload(
                    $scenario->fresh()
                ),
        ]);
    }

    public function duplicate(
        Request $request,
        WhatIfScenario $scenario,
    ): JsonResponse {
        $this->authorizeScenario(
            $request,
            $scenario
        );

        $copy =
            WhatIfScenario::query()
                ->create([
                    'user_id' =>
                        $request->user()->id,

                    'name' =>
                        $this->copyName(
                            $scenario->name
                        ),

                    'description' =>
                        $scenario->description,

                    'changes' =>
                        $scenario->changes,
                ]);

        return response()->json([
            'success' => true,
            'message' => 'Scenario duplicated.',
            'scenario' =>
                $this->scenarioPayload(
                    $copy
                ),
        ], 201);
    }

    public function destroy(
        Request $request,
        WhatIfScenario $scenario,
    ): JsonResponse {
        $this->authorizeScenario(
            $request,
            $scenario
        );

        $scenario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Scenario deleted.',
        ]);
    }

    private function validateScenario(
        Request $request,
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'changes' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'changes.*.action' => [
                'required',
                'string',
                Rule::in(
                    PortfolioChangeData::allowedActions()
                ),
            ],

            'changes.*.symbol' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9.\-]+$/',
            ],

            'changes.*.amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'changes.*.percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1',
            ],

            'changes.*.advisory_fee_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1',
            ],
        ]);
    }

    private function normalizeChanges(
        array $changes,
    ): array {
        return collect($changes)
            ->map(
                function (array $change): array {
                    return array_filter(
                        [
                            'action' =>
                                $change['action'],

                            'symbol' =>
                                isset(
                                    $change['symbol']
                                )
                                    ? strtoupper(
                                        trim(
                                            (string) $change['symbol']
                                        )
                                    )
                                    : null,

                            'amount' =>
                                isset(
                                    $change['amount']
                                )
                                    ? (float) $change['amount']
                                    : null,

                            'percentage' =>
                                isset(
                                    $change['percentage']
                                )
                                    ? (float) $change['percentage']
                                    : null,

                            'advisory_fee_rate' =>
                                isset(
                                    $change[
                                        'advisory_fee_rate'
                                    ]
                                )
                                    ? (float) $change[
                                        'advisory_fee_rate'
                                    ]
                                    : null,
                        ],
                        fn ($value) =>
                            $value !== null
                            && $value !== ''
                    );
                }
            )
            ->values()
            ->all();
    }

    private function authorizeScenario(
        Request $request,
        WhatIfScenario $scenario,
    ): void {
        abort_unless(
            (int) $scenario->user_id
            === (int) $request->user()->id,
            404
        );
    }

    private function scenarioPayload(
        WhatIfScenario $scenario,
    ): array {
        return [
            'id' =>
                $scenario->id,

            'name' =>
                $scenario->name,

            'description' =>
                $scenario->description,

            'changes' =>
                $scenario->changes
                ?? [],

            'change_count' =>
                count(
                    $scenario->changes
                    ?? []
                ),

            'created_at' =>
                $scenario->created_at
                    ?->toIso8601String(),

            'updated_at' =>
                $scenario->updated_at
                    ?->toIso8601String(),
        ];
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        return $value === ''
            ? null
            : $value;
    }

    private function copyName(
        string $name,
    ): string {
        $suffix = ' Copy';

        $maximumBaseLength =
            120 - strlen($suffix);

        return mb_substr(
            $name,
            0,
            $maximumBaseLength
        ) . $suffix;
    }
}