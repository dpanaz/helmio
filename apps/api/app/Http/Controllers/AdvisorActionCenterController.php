<?php

namespace App\Http\Controllers;

use App\Services\AdvisorAudit\AdvisorActionCenterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisorActionCenterController extends Controller
{
    public function index(
        Request $request,
        AdvisorActionCenterService $actionCenterService,
    ): View {
        $actionCenter =
            $actionCenterService->build(
                $request->user()->id
            );

        return view(
            'advisor-audit.action-center',
            [
                'actionCenter' =>
                    $actionCenter,
            ]
        );
    }
}