<?php

namespace App\Http\Controllers;

use App\Models\AuditFinding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuditFindingController extends Controller
{
    public function update(
        Request $request,
        AuditFinding $auditFinding,
    ): RedirectResponse {
        $this->authorizeFinding(
            $request,
            $auditFinding,
        );

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    AuditFinding::STATUS_OPEN,
                    AuditFinding::STATUS_REVIEWED,
                    AuditFinding::STATUS_DISMISSED,
                ]),
            ],

            'review_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $status = $validated['status'];

        $auditFinding->update([
            'status' => $status,

            'review_notes' =>
                $validated['review_notes']
                ?? $auditFinding->review_notes,

            'reviewed_at' =>
                $status === AuditFinding::STATUS_REVIEWED
                    ? now()
                    : null,

            'dismissed_at' =>
                $status === AuditFinding::STATUS_DISMISSED
                    ? now()
                    : null,

            'resolved_at' => null,
        ]);

        return back()->with(
            'success',
            'Finding status updated.',
        );
    }

    private function authorizeFinding(
        Request $request,
        AuditFinding $auditFinding,
    ): void {
        abort_unless(
            $auditFinding->user_id
                === $request->user()->id,
            403,
        );
    }
}