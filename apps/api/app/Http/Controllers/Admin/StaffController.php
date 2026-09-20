<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffRole;
use App\Models\StaffAuditLog;
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

        $this->audit($request, 'staff.employee.created', $user, [
            'role' => $role->slug,
        ]);

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

        if ($staffMember->hasStaffRole('admin')
            && $validated['role'] !== 'admin'
            && $this->activeAdminCount() <= 1) {
            return back()->withErrors([
                'role' => 'Helmio must always have at least one active administrator.',
            ]);
        }

        $role = StaffRole::query()->where('slug', $validated['role'])->firstOrFail();
        $previousRole = $staffMember->staffRoles->first()?->slug;
        $staffMember->staffRoles()->sync([$role->id]);

        $this->audit($request, 'staff.role.updated', $staffMember, [
            'previous_role' => $previousRole,
            'new_role' => $role->slug,
        ]);

        return back()->with('success', "{$staffMember->name}'s role was updated.");
    }

    public function resendInvite(Request $request, User $staffMember): RedirectResponse
    {
        abort_unless($staffMember->isStaff(), 404);

        $status = Password::sendResetLink(['email' => $staffMember->email]);
        $this->audit($request, 'staff.invitation.resent', $staffMember);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'warning',
            $status === Password::RESET_LINK_SENT
                ? "A password setup email was sent to {$staffMember->email}."
                : 'The password setup email could not be sent.',
        );
    }

    public function suspend(Request $request, User $staffMember): RedirectResponse
    {
        abort_unless($staffMember->isStaff(), 404);

        if ($request->user()->is($staffMember)) {
            return back()->withErrors(['staff' => 'You cannot suspend your own account.']);
        }

        if ($staffMember->hasStaffRole('admin') && $this->activeAdminCount() <= 1) {
            return back()->withErrors([
                'staff' => 'Helmio must always have at least one active administrator.',
            ]);
        }

        $staffMember->forceFill(['staff_suspended_at' => now()])->save();
        $staffMember->tokens()->delete();
        $this->audit($request, 'staff.employee.suspended', $staffMember);

        return back()->with('success', "{$staffMember->name}'s staff access was suspended.");
    }

    public function restore(Request $request, User $staffMember): RedirectResponse
    {
        abort_unless($staffMember->isStaff(), 404);

        $staffMember->forceFill(['staff_suspended_at' => null])->save();
        $this->audit($request, 'staff.employee.reactivated', $staffMember);

        return back()->with('success', "{$staffMember->name}'s staff access was restored.");
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->whereNull('staff_suspended_at')
            ->whereHas('staffRoles', fn ($query) => $query->where('slug', 'admin'))
            ->count();
    }

    private function audit(
        Request $request,
        string $event,
        User $staffMember,
        array $metadata = [],
    ): void {
        StaffAuditLog::query()->create([
            'actor_user_id' => $request->user()->id,
            'event' => $event,
            'route_name' => $request->route()?->getName(),
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'staff_user_id' => $staffMember->id,
                'staff_name' => $staffMember->name,
                'staff_email' => $staffMember->email,
                ...$metadata,
            ],
        ]);
    }
}
