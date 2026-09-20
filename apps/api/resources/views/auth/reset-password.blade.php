<x-guest-layout>
    <div class="mx-auto w-full max-w-md px-4 py-10 sm:px-6">
        <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-6 shadow-2xl shadow-black/30 backdrop-blur sm:p-8">
            <div class="mb-7">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-400">
                    Account security
                </p>

                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white">
                    Create your password
                </h1>

                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Choose a secure password for your Helmio account.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('password.store') }}"
                class="space-y-5"
            >
                @csrf

                <input
                    type="hidden"
                    name="token"
                    value="{{ $request->route('token') }}"
                >

                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-semibold text-slate-300"
                    >
                        Email address
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('email')
                        <p class="mt-2 text-sm text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password"
                        class="mb-2 block text-sm font-semibold text-slate-300"
                    >
                        New password
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500"
                    >

                    @error('password')
                        <p class="mt-2 text-sm text-red-400">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="password_confirmation"
                        class="mb-2 block text-sm font-semibold text-slate-300"
                    >
                        Confirm new password
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="block w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                <button
                    type="submit"
                    class="flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-slate-900"
                >
                    Set password and continue
                </button>
            </form>
        </div>

        <p class="mt-5 text-center text-xs text-slate-600">
            This secure link can only be used once.
        </p>
    </div>
</x-guest-layout>
