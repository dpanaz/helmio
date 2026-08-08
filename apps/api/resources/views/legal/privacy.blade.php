@extends('layouts.public')

@section('title', 'Privacy Policy — Helmio')

@section(
    'meta_description',
    'Learn how Helmio collects, uses, protects, and processes account, brokerage, portfolio, payment, and technical information.'
)

@section('content')

<section
    class="mx-auto max-w-7xl px-4 pb-12 pt-16 sm:px-6 sm:pb-16 sm:pt-20 lg:px-8"
>
    <div class="mx-auto max-w-4xl text-center">
        <p
            class="text-sm font-semibold uppercase tracking-widest text-blue-400"
        >
            Helmio Privacy
        </p>

        <h1
            class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl"
        >
            Privacy Policy
        </h1>

        <p
            class="mt-4 text-sm text-slate-500"
        >
            Last updated: August 2026
        </p>

        <p
            class="mx-auto mt-6 max-w-3xl text-lg leading-8 text-slate-400"
        >
            Your financial information deserves careful treatment.
            This policy explains what information Helmio may collect,
            how it is used, and how we work to protect it.
        </p>
    </div>
</section>

<section
    class="mx-auto max-w-6xl px-4 pb-12 sm:px-6 lg:px-8"
>
    <div
        class="grid gap-6 md:grid-cols-3"
    >
        <article
            class="rounded-3xl border border-slate-700 bg-slate-900 p-6"
        >
            <h2
                class="text-lg font-semibold text-white"
            >
                Read-only connections
            </h2>

            <p
                class="mt-3 text-sm leading-7 text-slate-400"
            >
                Brokerage connections are intended for monitoring
                and analysis. Helmio does not use them to place
                trades or move funds.
            </p>
        </article>

        <article
            class="rounded-3xl border border-slate-700 bg-slate-900 p-6"
        >
            <h2
                class="text-lg font-semibold text-white"
            >
                We don't sell your data
            </h2>

            <p
                class="mt-3 text-sm leading-7 text-slate-400"
            >
                Helmio does not sell personal information.
                Information may be shared with service providers
                when necessary to operate Helmio.
            </p>
        </article>

        <article
            class="rounded-3xl border border-slate-700 bg-slate-900 p-6"
        >
            <h2
                class="text-lg font-semibold text-white"
            >
                Controlled AI processing
            </h2>

            <p
                class="mt-3 text-sm leading-7 text-slate-400"
            >
                Relevant portfolio context may be processed by
                AI service providers when necessary to generate
                Helmio insights and explanations.
            </p>
        </article>
    </div>
</section>

<section
    class="border-t border-slate-800 bg-slate-900"
>
    <div
        class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8"
    >
        <article
            class="rounded-3xl border border-slate-700 bg-slate-950 p-7 shadow-2xl sm:p-10"
        >
            <div
                class="space-y-10 text-sm leading-7 text-slate-400"
            >
                <section>
                    <p
                        class="text-base leading-8 text-slate-300"
                    >
                        Helmio respects your privacy. This Privacy Policy
                        explains the types of information we may collect,
                        how we use it, and the choices available to you.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        1. Information We Collect
                    </h2>

                    <h3 class="mt-6 font-semibold text-slate-200">
                        Account Information
                    </h3>

                    <p class="mt-2">
                        When you create an account, we may collect
                        information such as your name, email address,
                        account credentials, and related account information.
                    </p>

                    <h3 class="mt-6 font-semibold text-slate-200">
                        Portfolio and Brokerage Information
                    </h3>

                    <p class="mt-2">
                        When you connect investment accounts, Helmio may
                        process information including balances, holdings,
                        securities, transactions, portfolio allocations,
                        performance history, account metadata, and
                        synchronization information.
                    </p>

                    <p class="mt-3">
                        Brokerage connections used by Helmio are intended
                        to operate with read-only permissions.
                    </p>

                    <h3 class="mt-6 font-semibold text-slate-200">
                        Payment Information
                    </h3>

                    <p class="mt-2">
                        Payments may be processed by Stripe or another
                        third-party payment provider. Helmio does not store
                        full payment-card details.
                    </p>

                    <h3 class="mt-6 font-semibold text-slate-200">
                        Technical Information
                    </h3>

                    <p class="mt-2">
                        We may collect IP address, browser type,
                        operating system, device information, usage activity,
                        security events, and application error logs.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        2. How We Use Information
                    </h2>

                    <p class="mt-4">
                        Information may be used to provide and improve Helmio,
                        calculate portfolio analytics, generate AI-powered
                        explanations, process subscriptions, provide support,
                        maintain security, prevent abuse, monitor application
                        reliability, and comply with legal obligations.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        3. AI Processing
                    </h2>

                    <p class="mt-4">
                        Helmio may securely provide relevant portfolio context
                        to third-party AI service providers for the purpose of
                        generating summaries, explanations, and portfolio insights.
                    </p>

                    <p class="mt-3">
                        Helmio is designed to limit AI processing to information
                        reasonably necessary to provide the requested service.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        4. Information Sharing
                    </h2>

                    <p class="mt-4 font-medium text-slate-300">
                        Helmio does not sell your personal information.
                    </p>

                    <p class="mt-3">
                        We may share information with service providers
                        when necessary to operate Helmio, including providers
                        supporting payment processing, cloud infrastructure,
                        brokerage connectivity, artificial intelligence,
                        customer communications, and security.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        5. Data Security
                    </h2>

                    <p class="mt-4">
                        We use administrative, technical, and organizational
                        safeguards designed to protect personal and financial
                        information.
                    </p>

                    <p class="mt-3">
                        No method of electronic transmission or storage
                        can be guaranteed to be completely secure.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        6. Data Retention
                    </h2>

                    <p class="mt-4">
                        We retain information for as long as reasonably
                        necessary to provide Helmio, maintain account records,
                        comply with legal obligations, resolve disputes,
                        prevent fraud, and enforce our agreements.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        7. Your Privacy Rights
                    </h2>

                    <p class="mt-4">
                        Depending on where you live, applicable law may
                        provide rights to access, correct, delete, obtain
                        a copy of, object to, or restrict certain processing
                        of your personal information.
                    </p>

                    <p class="mt-3">
                        Requests may be sent to
                        <a
                            href="mailto:contact@myhelmio.com"
                            class="font-semibold text-blue-400 hover:text-blue-300"
                        >
                            contact@myhelmio.com
                        </a>.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        8. Cookies
                    </h2>

                    <p class="mt-4">
                        Helmio may use cookies and similar technologies
                        to authenticate users, maintain sessions,
                        remember preferences, protect account security,
                        improve performance, and understand application usage.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        9. Children's Privacy
                    </h2>

                    <p class="mt-4">
                        Helmio is not intended for children under 18,
                        and we do not knowingly collect personal information
                        from children under 18.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        10. Changes to This Privacy Policy
                    </h2>

                    <p class="mt-4">
                        We may update this Privacy Policy from time to time.
                        Material changes will be reflected on this page
                        along with an updated effective date.
                    </p>
                </section>

                <section class="border-t border-slate-800 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        11. Contact
                    </h2>

                    <p class="mt-4">
                        Questions about this Privacy Policy may be sent to
                        <a
                            href="mailto:contact@myhelmio.com"
                            class="font-semibold text-blue-400 hover:text-blue-300"
                        >
                            contact@myhelmio.com
                        </a>.
                    </p>
                </section>
            </div>
        </article>
    </div>
</section>

@endsection