<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $customers = User::query()
            ->whereDoesntHave('staffRoles')
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    fn ($query) => $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'),
                ),
            )
            ->withCount([
                'investmentAccounts',
                'brokerageConnections',
                'askHelmioConversations',
            ])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function show(User $customer): View
    {
        abort_if($customer->isStaff(), 404);

        $customer->load([
            'investmentAccounts.institution',
            'brokerageConnections',
            'investorProfile',
            'subscriptions',
        ])->loadCount([
            'askHelmioConversations',
            'askHelmioMessages',
        ]);

        return view('admin.customers.show', compact('customer'));
    }
}
