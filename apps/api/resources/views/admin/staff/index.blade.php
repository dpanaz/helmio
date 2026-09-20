<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-4 border-b border-slate-800 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-400">Administration</p><h1 class="mt-2 text-3xl font-semibold text-white">Staff access</h1><p class="mt-2 text-sm text-slate-400">Create employee accounts and control which operations role each person has.</p></div>
                @if (auth()->user()->hasStaffPermission('audit.view'))<a href="{{ route('admin.audit.index') }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300">View staff audit log →</a>@endif
            </header>

            @if (session('success'))
                <div class="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="mb-6 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-300">{{ session('warning') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[0.9fr_1.4fr]">
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6">
                    <h2 class="text-lg font-semibold text-white">Create employee</h2>
                    <p class="mt-1 text-sm text-slate-400">The employee receives a one-time password setup email. Public registration never grants staff access.</p>

                    <form method="POST" action="{{ route('admin.staff.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-slate-300">Full name</label>
                            <input id="name" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-300">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="role" class="mb-2 block text-sm font-medium text-slate-300">Role</label>
                            <select id="role" name="role" required class="w-full rounded-xl border-slate-700 bg-slate-950 text-white focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->slug }}" @selected(old('role') === $role->slug)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="w-full rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500">Create employee and send invite</button>
                    </form>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    <div class="border-b border-slate-800 px-6 py-5">
                        <h2 class="text-lg font-semibold text-white">Current staff</h2>
                        <p class="mt-1 text-sm text-slate-400">{{ $staff->count() }} staff {{ Str::plural('account', $staff->count()) }}</p>
                    </div>
                    <div class="divide-y divide-slate-800">
                        @forelse ($staff as $member)
                            <div class="px-6 py-5 {{ $member->staff_suspended_at ? 'bg-red-500/[0.04]' : '' }}">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2"><p class="font-semibold text-white">{{ $member->name }}</p>@if ($member->staff_suspended_at)<span class="rounded-full bg-red-500/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-red-300">Suspended</span>@else<span class="rounded-full bg-emerald-500/10 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-300">Active</span>@endif</div>
                                    <p class="truncate text-sm text-slate-400">{{ $member->email }}</p>
                                    <p class="mt-2 text-xs text-slate-500">Last login: {{ $member->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.staff.update', $member) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="rounded-xl border-slate-700 bg-slate-950 text-sm text-white">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->slug }}" @selected($member->staffRoles->contains('slug', $role->slug))>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:border-blue-500 hover:text-white">Save</button>
                                </form>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-800 pt-4">
                                    <form method="POST" action="{{ route('admin.staff.invite', $member) }}">@csrf<button class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:border-blue-500 hover:text-white">Resend password setup</button></form>
                                    @if ($member->staff_suspended_at)
                                        <form method="POST" action="{{ route('admin.staff.restore', $member) }}">@csrf @method('PATCH')<button class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/20">Reactivate access</button></form>
                                    @elseif (! auth()->user()->is($member))
                                        <form method="POST" action="{{ route('admin.staff.suspend', $member) }}" onsubmit="return confirm('Suspend this employee from Helmio operations?')">@csrf @method('PATCH')<button class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-300 hover:bg-red-500/20">Suspend access</button></form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-6 py-10 text-center text-sm text-slate-500">No staff accounts yet.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
