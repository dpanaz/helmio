<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staff' => User::query()
                ->whereHas('staffRoles')
                ->with('staffRoles')
                ->orderBy('name')
                ->get(),
            'roles' => StaffRole::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::exists('staff_roles', 'slug')],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(64)),
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        $role = StaffRole::query()->where('slug', $validated['role'])->firstOrFail();
        $user->staffRoles()->sync([$role->id]);

        $status = Password::sendResetLink(['email' => $user->email]);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'warning',
            $status === Password::RESET_LINK_SENT
                ? "{$user->name} was created and sent a secure password setup link."
                : "{$user->name} was created, but the password setup email could not be sent. Use Forgot Password to resend it.",
        );
    }

    public function update(Request $request, User $staffMember): RedirectResponse
    {
        abort_unless($staffMember->isStaff(), 404);

        $validated = $request->validate([
            'role' => ['required', Rule::exists('staff_roles', 'slug')],
        ]);

        if ($request->user()->is($staffMember) && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own administrator access.',
            ]);
        }

        $role = StaffRole::query()->where('slug', $validated['role'])->firstOrFail();
        $staffMember->staffRoles()->sync([$role->id]);

        return back()->with('success', "{$staffMember->name}'s role was updated.");
    }
}
