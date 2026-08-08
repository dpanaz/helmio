@extends('layouts.public')

@section('title', 'Terms of Service')

@section('content')

<section class="mx-auto max-w-7xl px-4 pb-10 pt-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-400">
            Helmio Legal
        </p>

        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
            Terms of Service
        </h1>

        <p class="mt-4 text-sm text-slate-500">
            Last updated: August 2026
        </p>

        <p class="mx-auto mt-6 max-w-3xl text-lg leading-8 text-slate-400">
            These Terms of Service govern your use of Helmio's website,
            applications, software, and investment oversight platform.
        </p>
    </div>
</section>

<section class="border-t border-white/10 bg-slate-900/40">
    <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <article class="rounded-3xl border border-white/10 bg-slate-950/70 px-6 py-8 shadow-2xl shadow-black/10 sm:px-10 sm:py-12">

            <div class="space-y-12 text-sm leading-7 text-slate-400">

                <section>
                    <p class="text-base leading-8 text-slate-300">
                        Welcome to Helmio. These Terms of Service govern your use
                        of the Helmio website, applications, software, and related
                        services. By accessing or using Helmio, you agree to these
                        Terms.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        1. Our Service
                    </h2>

                    <p class="mt-4">
                        Helmio provides investment oversight tools, portfolio
                        analytics, reporting, monitoring, and AI-generated
                        explanations intended to help investors better understand
                        their portfolios.
                    </p>

                    <p class="mt-3">
                        Helmio is not a broker-dealer, investment adviser,
                        fiduciary, bank, tax adviser, law firm, or financial
                        institution.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        2. No Investment Advice
                    </h2>

                    <p class="mt-4">
                        Information provided by Helmio is for informational and
                        educational purposes only.
                    </p>

                    <p class="mt-3">
                        Nothing provided by Helmio constitutes investment advice,
                        financial planning, legal advice, tax advice, or a
                        recommendation to buy, sell, or hold any investment.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        3. Read-Only Access
                    </h2>

                    <p class="mt-4">
                        Helmio connects to brokerage accounts using read-only
                        access. Helmio cannot place trades, move money, withdraw
                        funds, or make changes to your accounts.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        4. Data Accuracy
                    </h2>

                    <p class="mt-4">
                        Portfolio information is supplied by third-party financial
                        institutions and market-data providers. While Helmio
                        strives for accuracy, we cannot guarantee that all
                        information is complete, current, or error free.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        5. AI Generated Content
                    </h2>

                    <p class="mt-4">
                        AI-generated summaries and insights are intended to help
                        explain portfolio activity and should never be considered
                        professional financial, legal, tax, or investment advice.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        6. User Responsibilities
                    </h2>

                    <p class="mt-4">
                        You are responsible for maintaining the security of your
                        account and for all activity performed using your login.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        7. Billing
                    </h2>

                    <p class="mt-4">
                        Premium features require an active subscription.
                        Subscription payments are processed through Stripe.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        8. Intellectual Property
                    </h2>

                    <p class="mt-4">
                        Helmio software, branding, analytics, reports, and
                        content are protected by applicable intellectual property
                        laws.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        9. Limitation of Liability
                    </h2>

                    <p class="mt-4">
                        Helmio shall not be liable for investment losses,
                        trading decisions, indirect damages, lost profits,
                        or reliance upon information provided through the
                        service.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        10. Governing Law
                    </h2>

                    <p class="mt-4">
                        These Terms are governed by the laws of the State
                        of Texas.
                    </p>
                </section>

                <section class="border-t border-white/10 pt-10">
                    <h2 class="text-2xl font-semibold text-white">
                        Contact
                    </h2>

                    <p class="mt-4">
                        Questions about these Terms may be sent to
                        <a
                            href="mailto:legal@myhelmio.com"
                            class="font-semibold text-blue-400 hover:text-blue-300"
                        >
                            legal@myhelmio.com
                        </a>.
                    </p>
                </section>

            </div>

        </article>
    </div>
</section>

@endsection