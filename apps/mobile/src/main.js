import './style.css';

import { Browser } from '@capacitor/browser';
import { Preferences } from '@capacitor/preferences';
import { SecureStorage } from '@aparajita/capacitor-secure-storage';
import { Device } from '@capacitor/device';


/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
|
| During Vite development we use the Vite /api proxy.
| Production Capacitor builds call myhelmio.com directly.
|
*/

const WEB_URL =
    import.meta.env.VITE_WEB_URL ||
    'https://myhelmio.com';

const API_URL =
    import.meta.env.DEV
        ? ''
        : (
            import.meta.env.VITE_API_URL ||
            'https://myhelmio.com'
        );

const AUTH_TOKEN_KEY =
    'helmio_auth_token';

const app =
    document.querySelector('#app');

let currentDashboard = null;

let currentAskConversation = null;
let currentAskConversations = [];
let askPollGeneration = 0;

let currentAccounts = [];
let currentAccountsSummary = null;
let currentAccountDetail = null;

let currentProfile = null;
let currentProfileAccountsSummary = null;


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatCurrency(value) {
    const number =
        Number(value ?? 0);

    if (!Number.isFinite(number)) {
        return '$0';
    }

    return new Intl.NumberFormat(
        'en-US',
        {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0,
        },
    ).format(number);
}

function normalizeScore(score) {
    if (
        score === null ||
        score === undefined ||
        score === '' ||
        Number.isNaN(Number(score))
    ) {
        return null;
    }

    return Math.max(
        0,
        Math.min(
            100,
            Number(score),
        ),
    );
}

function scoreClass(score) {
    const value =
        normalizeScore(score);

    if (value === null) {
        return 'score-neutral';
    }

    if (value >= 80) {
        return 'score-good';
    }

    if (value >= 60) {
        return 'score-fair';
    }

    if (value >= 40) {
        return 'score-warning';
    }

    return 'score-critical';
}

function severityClass(severity) {
    switch (
        String(severity ?? '')
            .toLowerCase()
    ) {
        case 'critical':
            return 'finding-critical';

        case 'important':
            return 'finding-important';

        case 'attention':
            return 'finding-attention';

        case 'good':
            return 'finding-good';

        default:
            return 'finding-neutral';
    }
}

function categoryIcon(key) {
    const icons = {
        cost: '$',
        diversification: '◫',
        performance: '↗',
        risk: '!',
        suitability: '◎',
        trading: '⇄',
        cash: '$',
        cash_drag: '$',
        tax: '%',
        tax_efficiency: '%',
    };

    return icons[key] || '•';
}

async function parseResponse(response) {
    const raw =
        await response.text();

    if (!raw) {
        return {};
    }

    try {
        return JSON.parse(raw);
    } catch {
        throw new Error(
            `Server returned ${response.status} with a non-JSON response.`,
        );
    }
}


/*
|--------------------------------------------------------------------------
| Token Storage
|--------------------------------------------------------------------------
*/

async function getToken() {
    try {
        const secureToken =
            await SecureStorage.get(
                AUTH_TOKEN_KEY,
            );

        if (
            typeof secureToken === 'string'
            && secureToken.length > 0
        ) {
            return secureToken;
        }
    } catch {
        // Continue to the legacy Preferences migration path.
    }

    /*
     * One-time migration for users who signed in before
     * secure storage was enabled. If a token exists in
     * Preferences, move it into Keychain / Keystore-backed
     * secure storage and remove the legacy copy.
     */
    try {
        const legacy =
            await Preferences.get({
                key: AUTH_TOKEN_KEY,
            });

        if (legacy.value) {
            await SecureStorage.set(
                AUTH_TOKEN_KEY,
                legacy.value,
            );

            await Preferences.remove({
                key: AUTH_TOKEN_KEY,
            });

            return legacy.value;
        }
    } catch {
        // No usable legacy token was found.
    }

    return null;
}

async function saveToken(token) {
    await SecureStorage.set(
        AUTH_TOKEN_KEY,
        token,
    );

    // Remove any legacy Preferences copy after a successful save.
    await Preferences.remove({
        key: AUTH_TOKEN_KEY,
    });
}

async function removeToken() {
    try {
        await SecureStorage.remove(
            AUTH_TOKEN_KEY,
        );
    } catch {
        // The secure token may already have been removed.
    }

    // Also remove any legacy Preferences token.
    await Preferences.remove({
        key: AUTH_TOKEN_KEY,
    });
}


/*
|--------------------------------------------------------------------------
| Authenticated API
|--------------------------------------------------------------------------
*/

async function authenticatedFetch(
    path,
    options = {},
) {
    const token =
        await getToken();

    if (!token) {
        throw new Error(
            'You are not signed in.',
        );
    }

    const headers = {
        Accept: 'application/json',
        ...options.headers,
        Authorization:
            `Bearer ${token}`,
    };

    const response =
        await fetch(
            `${API_URL}${path}`,
            {
                ...options,
                headers,
            },
        );

    if (response.status === 401) {
        await removeToken();

        currentDashboard = null;

        renderLogin();

        throw new Error(
            'Your session expired. Please sign in again.',
        );
    }

    return response;
}


/*
|--------------------------------------------------------------------------
| External Website Links
|--------------------------------------------------------------------------
*/

async function openSignup() {
    await Browser.open({
        url:
            `${WEB_URL}/register?source=mobile`,
    });
}

async function openPricing() {
    await Browser.open({
        url:
            `${WEB_URL}/pricing?source=mobile`,
    });
}

async function openForgotPassword() {
    await Browser.open({
        url:
            `${WEB_URL}/forgot-password?source=mobile`,
    });
}

async function openAskHelmio() {
    await loadAskHelmio();
}

async function openAccounts() {
    await loadAccounts();
}

async function openProfile() {
    await loadProfile();
}

async function openConnectAccount() {
    await Browser.open({
        url:
            `${WEB_URL}/accounts/connect`,
    });
}

async function openBilling() {
    await Browser.open({
        url: `${WEB_URL}/billing`,
    });
}

async function openWebProfile() {
    await Browser.open({
        url: `${WEB_URL}/profile`,
    });
}

async function openPrivacy() {
    await Browser.open({
        url: `${WEB_URL}/privacy`,
    });
}

async function openTerms() {
    await Browser.open({
        url: `${WEB_URL}/terms`,
    });
}

async function openSupport() {
    await Browser.open({
        url: `${WEB_URL}/contact`,
    });
}


/*
|--------------------------------------------------------------------------
| Branding
|--------------------------------------------------------------------------
*/

function renderBrand() {
    return `
        <div class="brand-lockup">
            <img
                src="/helmio-logo.png"
                alt="Helmio"
                class="brand-logo"
            >

            <span class="brand-name">
                HELMIO
            </span>
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
*/

function renderWelcome() {
    app.innerHTML = `
        <main class="welcome-screen">
            <section class="welcome-content">
                ${renderBrand()}

                <div class="welcome-copy">
                    <p class="eyebrow">
                        INDEPENDENT PORTFOLIO OVERSIGHT
                    </p>

                    <h1>
                        Know what's happening
                        inside your portfolio.
                    </h1>

                    <p class="welcome-description">
                        Helmio monitors your investments for
                        costs, risk, diversification,
                        performance and trading activity.
                    </p>
                </div>

                <section class="sample-score-card">
                    <div class="sample-score-ring">
                        <div>
                            <strong>
                                82
                            </strong>

                            <span>
                                / 100
                            </span>
                        </div>
                    </div>

                    <div>
                        <p class="sample-label">
                            SAMPLE HELM SCORE
                        </p>

                        <h2>
                            Very good
                        </h2>

                        <p>
                            One number summarizes the areas
                            Helmio continuously monitors.
                        </p>
                    </div>
                </section>

                <div class="welcome-actions">
                    <button
                        type="button"
                        class="primary-button"
                        data-action="sign-in"
                    >
                        Sign In
                    </button>

                    <button
                        type="button"
                        class="secondary-button"
                        data-action="create-account"
                    >
                        Create Account
                    </button>
                </div>

                <p class="welcome-security">
                    Read-only portfolio monitoring.
                    Helmio cannot move your money.
                </p>
            </section>
        </main>
    `;

    document
        .querySelector(
            '[data-action="sign-in"]',
        )
        ?.addEventListener(
            'click',
            renderLogin,
        );

    document
        .querySelector(
            '[data-action="create-account"]',
        )
        ?.addEventListener(
            'click',
            openSignup,
        );
}


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

function renderLogin() {
    app.innerHTML = `
        <main class="auth-screen">
            <section class="auth-card">
                ${renderBrand()}

                <div class="auth-heading">
                    <p class="eyebrow">
                        WELCOME BACK
                    </p>

                    <h1>
                        Sign in to Helmio
                    </h1>

                    <p>
                        Access your portfolio monitoring
                        and latest Helm Score.
                    </p>
                </div>

                <form
                    id="login-form"
                    class="login-form"
                >
                    <label>
                        <span>
                            Email
                        </span>

                        <input
                            type="email"
                            name="email"
                            autocomplete="email"
                            required
                            placeholder="you@example.com"
                        >
                    </label>

                    <label>
                        <span>
                            Password
                        </span>

                        <input
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            placeholder="Password"
                        >
                    </label>

                    <div
                        id="login-error"
                        class="login-error"
                        hidden
                    ></div>

                    <button
                        type="submit"
                        class="primary-button"
                        id="login-button"
                    >
                        Sign In
                    </button>
                </form>

                <button
                    type="button"
                    class="text-button"
                    data-action="forgot-password"
                >
                    Forgot password?
                </button>

                <div class="auth-divider">
                    <span>
                        New to Helmio?
                    </span>
                </div>

                <button
                    type="button"
                    class="secondary-button"
                    data-action="create-account"
                >
                    Create Account
                </button>

                <button
                    type="button"
                    class="back-link"
                    data-action="welcome"
                >
                    Back
                </button>
            </section>
        </main>
    `;

    document
        .querySelector('#login-form')
        ?.addEventListener(
            'submit',
            handleLogin,
        );

    document
        .querySelector(
            '[data-action="forgot-password"]',
        )
        ?.addEventListener(
            'click',
            openForgotPassword,
        );

    document
        .querySelector(
            '[data-action="create-account"]',
        )
        ?.addEventListener(
            'click',
            openSignup,
        );

    document
        .querySelector(
            '[data-action="welcome"]',
        )
        ?.addEventListener(
            'click',
            renderWelcome,
        );
}

async function handleLogin(event) {
    event.preventDefault();

    const form =
        event.currentTarget;

    const button =
        document.querySelector(
            '#login-button',
        );

    const errorElement =
        document.querySelector(
            '#login-error',
        );

    const formData =
        new FormData(form);

    const email =
        String(
            formData.get('email') ?? '',
        ).trim();

    const password =
        String(
            formData.get('password') ?? '',
        );

    button.disabled = true;
    button.textContent = 'Signing In...';

    errorElement.hidden = true;
    errorElement.textContent = '';

    try {
        let deviceName =
            'helmio-mobile';

        try {
            const deviceInfo =
                await Device.getInfo();

            deviceName =
                `helmio-${deviceInfo.platform || 'mobile'}`;
        } catch {
            // Browser development fallback.
        }

        const response =
            await fetch(
                `${API_URL}/api/mobile/login`,
                {
                    method: 'POST',

                    headers: {
                        Accept:
                            'application/json',

                        'Content-Type':
                            'application/json',
                    },

                    body:
                        JSON.stringify({
                            email,
                            password,
                            device_name:
                                deviceName,
                        }),
                },
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.errors?.email?.[0] ||
                data?.errors?.password?.[0] ||
                data?.message ||
                `Unable to sign in. Server returned ${response.status}.`,
            );
        }

        if (!data.token) {
            throw new Error(
                'Helmio did not return an authentication token.',
            );
        }

        await saveToken(
            data.token,
        );

        if (
            data?.subscription?.active
        ) {
            await loadDashboard();

            return;
        }

        renderSubscriptionRequired(
            data.user,
        );
    } catch (error) {
        errorElement.textContent =
            error?.message ||
            'Unable to sign in.';

        errorElement.hidden = false;
    } finally {
        button.disabled = false;
        button.textContent = 'Sign In';
    }
}


/*
|--------------------------------------------------------------------------
| Subscription
|--------------------------------------------------------------------------
*/

function renderSubscriptionRequired(user) {
    const firstName =
        String(
            user?.name ||
            'Investor',
        )
            .trim()
            .split(/\s+/)[0];

    app.innerHTML = `
        <main class="subscription-screen">
            <section class="subscription-content">
                ${renderBrand()}

                <div class="subscription-heading">
                    <h1>
                        You're signed in.
                    </h1>

                    <p>
                        An active Helmio subscription is
                        required to access portfolio monitoring.
                    </p>
                </div>

                <section class="subscription-card">
                    <p class="eyebrow">
                        ACCOUNT
                    </p>

                    <h2>
                        ${escapeHtml(firstName)}
                    </h2>

                    <p>
                        Complete your subscription securely
                        on myhelmio.com and then return
                        to the app.
                    </p>
                </section>

                <button
                    type="button"
                    class="primary-button"
                    data-action="pricing"
                >
                    View Subscription Options
                </button>

                <button
                    type="button"
                    class="text-button"
                    data-action="logout"
                >
                    Sign Out
                </button>
            </section>
        </main>
    `;

    document
        .querySelector(
            '[data-action="pricing"]',
        )
        ?.addEventListener(
            'click',
            openPricing,
        );

    document
        .querySelector(
            '[data-action="logout"]',
        )
        ?.addEventListener(
            'click',
            logout,
        );
}


/*
|--------------------------------------------------------------------------
| Loading / Error
|--------------------------------------------------------------------------
*/

function renderDashboardLoading() {
    app.innerHTML = `
        <main class="loading-screen">
            ${renderBrand()}

            <div class="loading-indicator">
                <span></span>
            </div>

            <h1>
                Reviewing your portfolio
            </h1>

            <p>
                Loading your latest Helmio analysis...
            </p>
        </main>
    `;
}

function renderDashboardError(message) {
    app.innerHTML = `
        <main class="error-screen">
            ${renderBrand()}

            <section class="error-card">
                <p class="eyebrow">
                    HELMIO
                </p>

                <h1>
                    We couldn't load your dashboard.
                </h1>

                <p>
                    ${escapeHtml(message)}
                </p>

                <button
                    type="button"
                    class="primary-button"
                    data-action="retry"
                >
                    Try Again
                </button>

                <button
                    type="button"
                    class="text-button"
                    data-action="logout"
                >
                    Sign Out
                </button>
            </section>
        </main>
    `;

    document
        .querySelector(
            '[data-action="retry"]',
        )
        ?.addEventListener(
            'click',
            loadDashboard,
        );

    document
        .querySelector(
            '[data-action="logout"]',
        )
        ?.addEventListener(
            'click',
            logout,
        );
}


/*
|--------------------------------------------------------------------------
| Dashboard API
|--------------------------------------------------------------------------
*/

async function loadDashboard() {
    renderDashboardLoading();

    try {
        const response =
            await authenticatedFetch(
                '/api/mobile/dashboard',
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.message ||
                `Unable to load dashboard. Server returned ${response.status}.`,
            );
        }

        currentDashboard =
            data;

        if (
            data.status ===
            'no_accounts'
        ) {
            renderNoAccounts(
                data,
            );

            return;
        }

        renderDashboard(
            data,
        );
    } catch (error) {
        if (
            String(
                error?.message ?? '',
            ).includes(
                'session expired',
            )
        ) {
            return;
        }

        renderDashboardError(
            error?.message ||
            'Unable to load your portfolio.',
        );
    }
}


/*
|--------------------------------------------------------------------------
| No Accounts
|--------------------------------------------------------------------------
*/

function renderNoAccounts(data) {
    const firstName =
        String(
            data?.user?.name ||
            'Investor',
        )
            .trim()
            .split(/\s+/)[0];

    app.innerHTML = `
        <main class="mobile-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="dashboard-welcome">
                <p class="eyebrow">
                    WELCOME BACK
                </p>

                <h1>
                    ${escapeHtml(firstName)}
                </h1>
            </section>

            <section class="empty-dashboard-card">
                <div class="empty-dashboard-icon">
                    +
                </div>

                <p class="eyebrow">
                    GET STARTED
                </p>

                <h2>
                    Connect your investment account
                </h2>

                <p>
                    Helmio needs read-only portfolio data
                    before it can calculate your Helm Score
                    and monitor your investments.
                </p>

                <button
                    type="button"
                    class="primary-button"
                    data-action="connect-account"
                >
                    Connect Account
                </button>
            </section>

            ${renderBottomNavigation('home')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="connect-account"]',
        )
        ?.addEventListener(
            'click',
            openConnectAccount,
        );

    bindBottomNavigation();
}


/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

function renderDashboard(data) {
    currentDashboard =
        data;

    const firstName =
        String(
            data?.user?.name ||
            'Investor',
        )
            .trim()
            .split(/\s+/)[0];

    const score =
        normalizeScore(
            data?.helm_score?.score,
        );

    const scoreDisplay =
        score === null
            ? '—'
            : Math.round(score);

    const scoreLabel =
        data?.helm_score?.label ||
        'Building your score';

    const completeness =
        Math.round(
            Math.max(
                0,
                Math.min(
                    1,
                    Number(
                        data?.helm_score
                            ?.data_completeness ??
                        0,
                    ),
                ),
            ) * 100,
        );

    const portfolioValue =
        data?.portfolio?.value ??
        0;

    const accountCount =
        data?.portfolio
            ?.account_count ??
        0;

    const findings =
        Array.isArray(
            data?.findings,
        )
            ? data.findings
            : [];

    app.innerHTML = `
        <main class="mobile-shell dashboard-shell">
            <header class="mobile-header">
                ${renderBrand()}

                <button
                    type="button"
                    class="header-icon-button"
                    data-action="refresh"
                    aria-label="Refresh dashboard"
                >
                    ↻
                </button>
            </header>

            <section class="dashboard-welcome">
                <p class="eyebrow">
                    WELCOME BACK
                </p>

                <h1>
                    ${escapeHtml(firstName)}
                </h1>
            </section>

            <section class="helm-score-card">
                <div class="helm-score-card-header">
                    <div>
                        <p class="eyebrow">
                            PORTFOLIO HEALTH
                        </p>

                        <h2>
                            Helm Score
                        </h2>
                    </div>

                    <span class="completeness">
                        ${completeness}% complete
                    </span>
                </div>

                <div class="helm-score-body">
                    <div
                        class="helm-score-ring"
                        style="
                            --helm-score:
                            ${score ?? 0};
                        "
                    >
                        <div class="helm-score-center">
                            <strong
                                class="${scoreClass(score)}"
                            >
                                ${scoreDisplay}
                            </strong>

                            <span>
                                / 100
                            </span>
                        </div>
                    </div>

                    <div class="helm-score-copy">
                        <h3>
                            ${escapeHtml(scoreLabel)}
                        </h3>

                        <p>
                            Your Helm Score summarizes
                            the areas Helmio monitors
                            across your portfolio.
                        </p>
                    </div>
                </div>
            </section>

            <section class="portfolio-summary-grid">
                <article class="summary-card">
                    <p>
                        PORTFOLIO VALUE
                    </p>

                    <strong>
                        ${formatCurrency(portfolioValue)}
                    </strong>
                </article>

                <article class="summary-card">
                    <p>
                        ACCOUNTS
                    </p>

                    <strong>
                        ${escapeHtml(accountCount)}
                    </strong>
                </article>
            </section>

            <section class="findings-section">
                <div class="section-heading-row">
                    <div>
                        <p class="eyebrow">
                            OVERSIGHT
                        </p>

                        <h2>
                            Top Findings
                        </h2>
                    </div>

                    <span>
                        ${findings.length}
                    </span>
                </div>

                <div class="findings-list">
                    ${
                        findings.length
                            ? findings
                                .map(
                                    renderFindingCard,
                                )
                                .join('')
                            : `
                                <article class="finding-card finding-good">
                                    <p class="finding-category">
                                        CURRENT REVIEW
                                    </p>

                                    <h3>
                                        No priority findings
                                    </h3>

                                    <p>
                                        Helmio has not identified
                                        an issue requiring immediate
                                        review.
                                    </p>
                                </article>
                            `
                    }
                </div>
            </section>

            ${renderBottomNavigation('home')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="refresh"]',
        )
        ?.addEventListener(
            'click',
            loadDashboard,
        );

    document
        .querySelectorAll(
            '[data-finding-category]',
        )
        .forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    () => {
                        const key =
                            button.dataset
                                .findingCategory;

                        openCategoryByKey(
                            key,
                        );
                    },
                );
            },
        );

    bindBottomNavigation();
}

function renderFindingCard(finding) {
    const score =
        normalizeScore(
            finding?.score,
        );

    const categoryName =
        finding?.category_name ||
        finding?.category ||
        'Portfolio';

    const label =
        finding?.label ||
        'Review recommended';

    const message =
        finding?.message ||
        'Review this portfolio finding.';

    const recommendation =
        finding?.recommendation;

    return `
        <button
            type="button"
            class="
                finding-card
                ${severityClass(finding?.severity)}
            "
            data-finding-category="${escapeHtml(
                finding?.category || '',
            )}"
        >
            <div class="finding-card-header">
                <p class="finding-category">
                    ${escapeHtml(
                        String(
                            categoryName,
                        ).toUpperCase(),
                    )}
                </p>

                ${
                    score !== null
                        ? `
                            <span
                                class="
                                    finding-score
                                    ${scoreClass(score)}
                                "
                            >
                                ${Math.round(score)}
                            </span>
                        `
                        : ''
                }
            </div>

            <h3>
                ${escapeHtml(label)}
            </h3>

            <p>
                ${escapeHtml(message)}
            </p>

            ${
                recommendation
                    ? `
                        <div class="finding-recommendation">
                            ${escapeHtml(
                                recommendation,
                            )}
                        </div>
                    `
                    : ''
            }
        </button>
    `;
}



/*
|--------------------------------------------------------------------------
| Native Accounts
|--------------------------------------------------------------------------
*/

function formatAccountType(value) {
    const normalized =
        String(value || '')
            .trim()
            .replaceAll('_', ' ')
            .replaceAll('-', ' ');

    if (!normalized) {
        return 'Investment account';
    }

    return normalized.replace(
        /\b\w/g,
        (letter) => letter.toUpperCase(),
    );
}

function formatAccountMask(mask) {
    const value =
        String(mask || '').trim();

    if (!value) {
        return '';
    }

    return ` •••• ${escapeHtml(value)}`;
}

function renderAccountsLoading() {
    app.innerHTML = `
        <main class="mobile-shell accounts-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="accounts-heading">
                <p class="eyebrow">
                    YOUR MONEY
                </p>

                <h1>
                    Accounts
                </h1>

                <p>
                    Loading your connected investment
                    accounts and holdings.
                </p>
            </section>

            <section class="accounts-loading-card">
                <div class="accounts-loading-row">
                    <span></span>
                    <span></span>
                </div>

                <div class="accounts-loading-row">
                    <span></span>
                    <span></span>
                </div>
            </section>

            ${renderBottomNavigation('accounts')}
        </main>
    `;

    bindBottomNavigation();
}

function renderAccountsError(message) {
    app.innerHTML = `
        <main class="mobile-shell accounts-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="accounts-heading">
                <p class="eyebrow">
                    YOUR MONEY
                </p>

                <h1>
                    Accounts
                </h1>
            </section>

            <section class="accounts-empty-card">
                <div class="accounts-empty-icon">
                    !
                </div>

                <h2>
                    Unable to load accounts
                </h2>

                <p>
                    ${escapeHtml(
                        message ||
                        'Please try again.',
                    )}
                </p>

                <button
                    type="button"
                    class="primary-button"
                    data-action="retry-accounts"
                >
                    Try Again
                </button>
            </section>

            ${renderBottomNavigation('accounts')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="retry-accounts"]',
        )
        ?.addEventListener(
            'click',
            loadAccounts,
        );

    bindBottomNavigation();
}

async function loadAccounts() {
    renderAccountsLoading();

    try {
        const response =
            await authenticatedFetch(
                '/api/mobile/accounts',
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.message ||
                `Unable to load accounts. Server returned ${response.status}.`,
            );
        }

        currentAccounts =
            Array.isArray(data?.accounts)
                ? data.accounts
                : [];

        currentAccountsSummary =
            data?.summary || {
                account_count:
                    currentAccounts.length,
                total_value:
                    currentAccounts.reduce(
                        (sum, account) =>
                            sum +
                            Number(
                                account?.value || 0,
                            ),
                        0,
                    ),
            };

        currentAccountDetail = null;

        renderAccounts();
    } catch (error) {
        renderAccountsError(
            error?.message ||
            'Unable to load accounts.',
        );
    }
}

function renderAccounts() {
    const accountCount =
        Number(
            currentAccountsSummary
                ?.account_count ??
            currentAccounts.length,
        );

    const totalValue =
        Number(
            currentAccountsSummary
                ?.total_value ??
            0,
        );

    app.innerHTML = `
        <main class="mobile-shell accounts-shell">
            <header class="mobile-header">
                ${renderBrand()}

                <button
                    type="button"
                    class="header-icon-button"
                    data-action="refresh-accounts"
                    aria-label="Refresh accounts"
                >
                    ↻
                </button>
            </header>

            <section class="accounts-heading">
                <p class="eyebrow">
                    YOUR MONEY
                </p>

                <h1>
                    Accounts
                </h1>

                <p>
                    A consolidated view of the
                    investment accounts Helmio
                    is monitoring.
                </p>
            </section>

            <section class="accounts-summary-card">
                <span class="accounts-summary-label">
                    TOTAL PORTFOLIO VALUE
                </span>

                <strong>
                    ${formatCurrency(totalValue)}
                </strong>

                <span class="accounts-summary-meta">
                    ${accountCount}
                    ${accountCount === 1
                        ? 'account'
                        : 'accounts'}
                    monitored
                </span>
            </section>

            <section class="accounts-list">
                ${
                    currentAccounts.length
                        ? currentAccounts
                            .map(
                                renderAccountCard,
                            )
                            .join('')
                        : `
                            <div class="accounts-empty-card">
                                <div class="accounts-empty-icon">
                                    +
                                </div>

                                <h2>
                                    No accounts connected
                                </h2>

                                <p>
                                    Connect an investment account
                                    to start monitoring holdings,
                                    fees, performance and risk.
                                </p>

                                <button
                                    type="button"
                                    class="primary-button"
                                    data-action="connect-account"
                                >
                                    Connect Account
                                </button>
                            </div>
                        `
                }
            </section>

            ${
                currentAccounts.length
                    ? `
                        <button
                            type="button"
                            class="secondary-full-button accounts-manage-button"
                            data-action="manage-accounts"
                        >
                            Connect or manage accounts
                        </button>
                    `
                    : ''
            }

            ${renderBottomNavigation('accounts')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="refresh-accounts"]',
        )
        ?.addEventListener(
            'click',
            loadAccounts,
        );

    document
        .querySelector(
            '[data-action="connect-account"]',
        )
        ?.addEventListener(
            'click',
            openConnectAccount,
        );

    document
        .querySelector(
            '[data-action="manage-accounts"]',
        )
        ?.addEventListener(
            'click',
            openConnectAccount,
        );

    document
        .querySelectorAll(
            '[data-account-id]',
        )
        .forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    () => {
                        loadAccountDetail(
                            button.dataset.accountId,
                        );
                    },
                );
            },
        );

    bindBottomNavigation();
}

function renderAccountCard(account) {
    const institutionName =
        account?.institution?.name ||
        'Investment Account';

    const accountName =
        account?.name ||
        institutionName;

    const accountType =
        formatAccountType(
            account?.account_type,
        );

    const mask =
        formatAccountMask(
            account?.account_number_mask,
        );

    const holdingCount =
        Number(
            account?.holding_count || 0,
        );

    return `
        <button
            type="button"
            class="native-account-card"
            data-account-id="${escapeHtml(
                account?.id,
            )}"
        >
            <div class="native-account-card-top">
                <div>
                    <span class="native-account-institution">
                        ${escapeHtml(
                            institutionName,
                        )}
                    </span>

                    <h2>
                        ${escapeHtml(
                            accountName,
                        )}
                    </h2>

                    <p>
                        ${escapeHtml(
                            accountType,
                        )}${mask}
                    </p>
                </div>

                <span class="native-account-chevron">
                    ›
                </span>
            </div>

            <div class="native-account-card-bottom">
                <div>
                    <span>
                        ACCOUNT VALUE
                    </span>

                    <strong>
                        ${formatCurrency(
                            account?.value,
                        )}
                    </strong>
                </div>

                <div class="native-account-holdings-count">
                    <span>
                        HOLDINGS
                    </span>

                    <strong>
                        ${holdingCount}
                    </strong>
                </div>
            </div>
        </button>
    `;
}

async function loadAccountDetail(
    accountId,
) {
    if (!accountId) {
        return;
    }

    app.innerHTML = `
        <main class="mobile-shell accounts-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="account-detail-loading">
                <div class="loading-indicator">
                    <span></span>
                </div>

                <p>
                    Loading account details…
                </p>
            </section>

            ${renderBottomNavigation('accounts')}
        </main>
    `;

    bindBottomNavigation();

    try {
        const response =
            await authenticatedFetch(
                `/api/mobile/accounts/${encodeURIComponent(
                    accountId,
                )}`,
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.message ||
                `Unable to load account. Server returned ${response.status}.`,
            );
        }

        currentAccountDetail =
            data?.account || null;

        renderAccountDetail(
            currentAccountDetail,
        );
    } catch (error) {
        renderAccountsError(
            error?.message ||
            'Unable to load this account.',
        );
    }
}

function renderAccountDetail(account) {
    if (!account) {
        renderAccountsError(
            'Account details were not available.',
        );

        return;
    }

    const holdings =
        Array.isArray(account?.holdings)
            ? account.holdings
            : [];

    const institutionName =
        account?.institution?.name ||
        'Investment Account';

    app.innerHTML = `
        <main class="mobile-shell accounts-shell account-detail-shell">
            <header class="mobile-header">
                <button
                    type="button"
                    class="account-back-button"
                    data-action="back-to-accounts"
                    aria-label="Back to accounts"
                >
                    ←
                </button>

                ${renderBrand()}

                <span class="account-header-spacer"></span>
            </header>

            <section class="account-detail-heading">
                <p class="eyebrow">
                    ${escapeHtml(
                        institutionName,
                    )}
                </p>

                <h1>
                    ${escapeHtml(
                        account?.name ||
                        institutionName,
                    )}
                </h1>

                <p>
                    ${escapeHtml(
                        formatAccountType(
                            account?.account_type,
                        ),
                    )}${formatAccountMask(
                        account?.account_number_mask,
                    )}
                </p>
            </section>

            <section class="account-value-card">
                <span>
                    ACCOUNT VALUE
                </span>

                <strong>
                    ${formatCurrency(
                        account?.value,
                    )}
                </strong>

                <div class="account-value-breakdown">
                    <div>
                        <span>
                            Holdings
                        </span>

                        <strong>
                            ${formatCurrency(
                                account?.holdings_value,
                            )}
                        </strong>
                    </div>

                    <div>
                        <span>
                            Cash
                        </span>

                        <strong>
                            ${formatCurrency(
                                account?.cash_value,
                            )}
                        </strong>
                    </div>
                </div>
            </section>

            <section class="holdings-section">
                <div class="holdings-section-header">
                    <div>
                        <p class="eyebrow">
                            POSITIONS
                        </p>

                        <h2>
                            Holdings
                        </h2>
                    </div>

                    <span>
                        ${holdings.length}
                    </span>
                </div>

                <div class="holdings-list">
                    ${
                        holdings.length
                            ? holdings
                                .map(
                                    renderHoldingRow,
                                )
                                .join('')
                            : `
                                <div class="holdings-empty">
                                    No positive-value holdings
                                    are currently available for
                                    this account.
                                </div>
                            `
                    }
                </div>
            </section>

            <button
                type="button"
                class="secondary-full-button accounts-manage-button"
                data-action="manage-accounts"
            >
                Manage connected account
            </button>

            ${renderBottomNavigation('accounts')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="back-to-accounts"]',
        )
        ?.addEventListener(
            'click',
            renderAccounts,
        );

    document
        .querySelector(
            '[data-action="manage-accounts"]',
        )
        ?.addEventListener(
            'click',
            openConnectAccount,
        );

    bindBottomNavigation();
}

function renderHoldingRow(holding) {
    const symbol =
        holding?.symbol ||
        '—';

    const name =
        holding?.name ||
        'Security';

    const weight =
        Number(
            holding?.weight || 0,
        );

    const quantity =
        Number(
            holding?.quantity || 0,
        );

    const gainLoss =
        holding?.unrealized_gain_loss;

    const gainLossNumber =
        gainLoss === null ||
        gainLoss === undefined
            ? null
            : Number(gainLoss);

    const gainLossClass =
        gainLossNumber === null
            ? ''
            : (
                gainLossNumber >= 0
                    ? 'positive'
                    : 'negative'
            );

    return `
        <article class="holding-row">
            <div class="holding-row-main">
                <div class="holding-symbol-badge">
                    ${escapeHtml(
                        symbol.slice(0, 5),
                    )}
                </div>

                <div class="holding-identity">
                    <strong>
                        ${escapeHtml(
                            symbol,
                        )}
                    </strong>

                    <span>
                        ${escapeHtml(
                            name,
                        )}
                    </span>
                </div>

                <div class="holding-value">
                    <strong>
                        ${formatCurrency(
                            holding?.market_value,
                        )}
                    </strong>

                    <span>
                        ${weight.toFixed(1)}%
                    </span>
                </div>
            </div>

            <div class="holding-row-meta">
                <span>
                    ${quantity.toLocaleString(
                        'en-US',
                        {
                            maximumFractionDigits: 4,
                        },
                    )} shares
                </span>

                ${
                    gainLossNumber === null
                        ? ''
                        : `
                            <span class="${gainLossClass}">
                                ${gainLossNumber >= 0
                                    ? '+'
                                    : ''}${formatCurrency(
                                        gainLossNumber,
                                    )}
                                unrealized
                            </span>
                        `
                }
            </div>
        </article>
    `;
}



/*
|--------------------------------------------------------------------------
| Native Profile
|--------------------------------------------------------------------------
*/

function profileInitials(user) {
    const name = String(user?.name || '').trim();

    if (!name) {
        return 'H';
    }

    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

function renderProfileLoading() {
    app.innerHTML = `
        <main class="mobile-shell profile-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="profile-heading">
                <p class="eyebrow">YOUR HELMIO</p>
                <h1>Profile</h1>
                <p>Loading your account settings.</p>
            </section>

            <section class="profile-loading-card">
                <div class="profile-loading-avatar"></div>
                <div class="profile-loading-lines">
                    <span></span>
                    <span></span>
                </div>
            </section>

            ${renderBottomNavigation('profile')}
        </main>
    `;

    bindBottomNavigation();
}

function renderProfileError(message) {
    app.innerHTML = `
        <main class="mobile-shell profile-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="profile-heading">
                <p class="eyebrow">YOUR HELMIO</p>
                <h1>Profile</h1>
            </section>

            <section class="profile-error-card">
                <div class="profile-error-icon">!</div>
                <h2>Unable to load profile</h2>
                <p>${escapeHtml(message || 'Please try again.')}</p>
                <button type="button" class="primary-button" data-action="retry-profile">
                    Try Again
                </button>
            </section>

            ${renderBottomNavigation('profile')}
        </main>
    `;

    document
        .querySelector('[data-action="retry-profile"]')
        ?.addEventListener('click', loadProfile);

    bindBottomNavigation();
}

async function loadProfile() {
    renderProfileLoading();

    try {
        const response =
            await authenticatedFetch('/api/mobile/me');

        const data =
            await parseResponse(response);

        if (!response.ok) {
            throw new Error(
                data?.message ||
                `Unable to load profile. Server returned ${response.status}.`,
            );
        }

        currentProfile = data;
        currentProfileAccountsSummary = null;

        try {
            const accountsResponse =
                await authenticatedFetch('/api/mobile/accounts');

            const accountsData =
                await parseResponse(accountsResponse);

            if (accountsResponse.ok) {
                currentProfileAccountsSummary =
                    accountsData?.summary || null;
            }
        } catch {
            // Supplemental profile data should not block the profile screen.
        }

        renderProfile();
    } catch (error) {
        renderProfileError(
            error?.message ||
            'Unable to load your Helmio profile.',
        );
    }
}

function renderProfileRow({
    action,
    icon,
    title,
    subtitle = '',
    value = '',
    danger = false,
}) {
    return `
        <button
            type="button"
            class="profile-menu-row${danger ? ' danger' : ''}"
            data-action="${escapeHtml(action)}"
        >
            <span class="profile-menu-icon" aria-hidden="true">
                ${escapeHtml(icon)}
            </span>

            <span class="profile-menu-copy">
                <strong>${escapeHtml(title)}</strong>
                ${subtitle ? `<small>${escapeHtml(subtitle)}</small>` : ''}
            </span>

            ${value ? `<span class="profile-menu-value">${escapeHtml(value)}</span>` : ''}
            <span class="profile-menu-chevron" aria-hidden="true">›</span>
        </button>
    `;
}

function renderProfile() {
    if (!currentProfile?.user) {
        loadProfile();
        return;
    }

    const user = currentProfile.user;
    const subscriptionActive =
        Boolean(currentProfile?.subscription?.active);

    const accountCount =
        Number(currentProfileAccountsSummary?.account_count ?? 0);

    const accountValue =
        Number(currentProfileAccountsSummary?.total_value ?? 0);

    app.innerHTML = `
        <main class="mobile-shell profile-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="profile-heading">
                <p class="eyebrow">YOUR HELMIO</p>
                <h1>Profile</h1>
                <p>Manage your Helmio account and app settings.</p>
            </section>

            <section class="profile-identity-card">
                <div class="profile-avatar">
                    ${escapeHtml(profileInitials(user))}
                </div>

                <div class="profile-identity-copy">
                    <strong>${escapeHtml(user?.name || 'Helmio Member')}</strong>
                    <span>${escapeHtml(user?.email || '')}</span>
                </div>
            </section>

            <section class="profile-membership-card">
                <div class="profile-membership-topline">
                    <div>
                        <span class="profile-section-kicker">HELMIO MEMBERSHIP</span>
                        <h2>${subscriptionActive ? 'Active' : 'Action needed'}</h2>
                    </div>

                    <span class="profile-status-pill ${subscriptionActive ? 'active' : 'inactive'}">
                        <i></i>
                        ${subscriptionActive ? 'Active' : 'Inactive'}
                    </span>
                </div>

                <p>
                    ${subscriptionActive
                        ? 'Your Helmio monitoring subscription is active.'
                        : 'Review your subscription to continue Helmio monitoring.'}
                </p>

                <button type="button" class="profile-inline-button" data-action="profile-billing">
                    Manage subscription <span>›</span>
                </button>
            </section>

            <section class="profile-mini-stats">
                <button type="button" data-action="profile-accounts">
                    <span>CONNECTED ACCOUNTS</span>
                    <strong>${accountCount}</strong>
                </button>

                <button type="button" data-action="profile-accounts">
                    <span>MONITORED VALUE</span>
                    <strong>${formatCurrency(accountValue)}</strong>
                </button>
            </section>

            <section class="profile-menu-section">
                <p class="profile-section-label">ACCOUNT</p>
                <div class="profile-menu-card">
                    ${renderProfileRow({
                        action: 'profile-accounts',
                        icon: '◎',
                        title: 'Connected accounts',
                        subtitle: accountCount === 1
                            ? '1 account connected'
                            : `${accountCount} accounts connected`,
                    })}
                    ${renderProfileRow({
                        action: 'profile-security',
                        icon: '⌾',
                        title: 'Security & password',
                        subtitle: 'Manage login and account security',
                    })}
                    ${renderProfileRow({
                        action: 'profile-billing',
                        icon: '$',
                        title: 'Billing',
                        subtitle: 'Subscription and payment settings',
                    })}
                </div>
            </section>

            <section class="profile-menu-section">
                <p class="profile-section-label">HELMIO</p>
                <div class="profile-menu-card">
                    ${renderProfileRow({ action: 'profile-support', icon: '?', title: 'Help & support' })}
                    ${renderProfileRow({ action: 'profile-privacy', icon: '◇', title: 'Privacy policy' })}
                    ${renderProfileRow({ action: 'profile-terms', icon: '≡', title: 'Terms of service' })}
                </div>
            </section>

            <section class="profile-trust-note">
                <span>READ-ONLY OVERSIGHT</span>
                <p>
                    Helmio monitors investment data and does not have trading authority over connected accounts.
                </p>
            </section>

            <button type="button" class="profile-signout-button" data-action="profile-signout">
                Sign Out
            </button>

            ${renderBottomNavigation('profile')}
        </main>
    `;

    document
        .querySelectorAll('[data-action="profile-accounts"]')
        .forEach((element) => {
            element.addEventListener('click', loadAccounts);
        });

    document
        .querySelectorAll('[data-action="profile-billing"]')
        .forEach((element) => {
            element.addEventListener('click', openBilling);
        });

    document
        .querySelector('[data-action="profile-security"]')
        ?.addEventListener('click', openWebProfile);

    document
        .querySelector('[data-action="profile-support"]')
        ?.addEventListener('click', openSupport);

    document
        .querySelector('[data-action="profile-privacy"]')
        ?.addEventListener('click', openPrivacy);

    document
        .querySelector('[data-action="profile-terms"]')
        ?.addEventListener('click', openTerms);

    document
        .querySelector('[data-action="profile-signout"]')
        ?.addEventListener('click', logout);

    bindBottomNavigation();
}

/*
|--------------------------------------------------------------------------
| Native Analysis
|--------------------------------------------------------------------------
*/

function getDashboardCategories() {
    if (!currentDashboard) {
        return [];
    }

    const categories =
        currentDashboard.categories;

    if (
        Array.isArray(
            categories,
        )
    ) {
        return categories;
    }

    if (
        categories &&
        typeof categories ===
            'object'
    ) {
        return Object.entries(
            categories,
        ).map(
            ([key, value]) => ({
                key,
                ...(value || {}),
            }),
        );
    }

    return [];
}

function renderAnalysis() {
    if (!currentDashboard) {
        loadDashboard();

        return;
    }

    const categories =
        getDashboardCategories();

    const overallScore =
        normalizeScore(
            currentDashboard
                ?.helm_score
                ?.score,
        );

    const overallLabel =
        currentDashboard
            ?.helm_score
            ?.label ||
        'Building your score';

    app.innerHTML = `
        <main class="mobile-shell analysis-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="analysis-heading">
                <p class="eyebrow">
                    PORTFOLIO ANALYSIS
                </p>

                <h1>
                    Your Portfolio Health
                </h1>

                <p>
                    See what Helmio is monitoring
                    and where your portfolio may
                    need attention.
                </p>
            </section>

            <section class="analysis-score-summary">
                <div>
                    <span class="summary-label">
                        HELM SCORE
                    </span>

                    <div class="summary-score-row">
                        <strong
                            class="${scoreClass(overallScore)}"
                        >
                            ${
                                overallScore === null
                                    ? '—'
                                    : Math.round(
                                        overallScore,
                                    )
                            }
                        </strong>

                        <span>
                            ${escapeHtml(
                                overallLabel,
                            )}
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    class="analysis-refresh-button"
                    data-action="refresh-analysis"
                    aria-label="Refresh analysis"
                >
                    ↻
                </button>
            </section>

            <section class="analysis-category-list">
                ${
                    categories.length
                        ? categories
                            .map(
                                renderAnalysisCategory,
                            )
                            .join('')
                        : `
                            <div class="analysis-empty-card">
                                <h2>
                                    No analysis available yet
                                </h2>

                                <p>
                                    Helmio will populate
                                    this screen as portfolio
                                    data becomes available.
                                </p>
                            </div>
                        `
                }
            </section>

            ${renderBottomNavigation('analysis')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="refresh-analysis"]',
        )
        ?.addEventListener(
            'click',
            refreshAnalysis,
        );

    document
        .querySelectorAll(
            '[data-category]',
        )
        .forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    () => {
                        openCategoryByKey(
                            button.dataset.category,
                        );
                    },
                );
            },
        );

    bindBottomNavigation();
}

function renderAnalysisCategory(
    category,
) {
    const score =
        normalizeScore(
            category?.score,
        );

    const key =
        category?.key || '';

    const name =
        category?.name ||
        key
            .replaceAll('_', ' ')
            .replace(
                /\b\w/g,
                (letter) =>
                    letter.toUpperCase(),
            ) ||
        'Analysis';

    const reasons =
        Array.isArray(
            category?.reasons,
        )
            ? category.reasons
            : [];

    const scoreWidth =
        score === null
            ? 0
            : score;

    return `
        <button
            type="button"
            class="analysis-category-card"
            data-category="${escapeHtml(key)}"
        >
            <div class="analysis-category-top">
                <div class="analysis-category-identity">
                    <span class="analysis-category-icon">
                        ${categoryIcon(key)}
                    </span>

                    <div>
                        <h2>
                            ${escapeHtml(name)}
                        </h2>

                        <p
                            class="${scoreClass(score)}"
                        >
                            ${escapeHtml(
                                category?.label ||
                                'Insufficient data',
                            )}
                        </p>
                    </div>
                </div>

                <div
                    class="
                        analysis-category-score
                        ${scoreClass(score)}
                    "
                >
                    ${
                        score === null
                            ? '—'
                            : Math.round(score)
                    }
                </div>
            </div>

            <div class="analysis-progress-track">
                <div
                    class="
                        analysis-progress-fill
                        ${scoreClass(score)}
                    "
                    style="
                        width:
                        ${scoreWidth}%;
                    "
                ></div>
            </div>

            <div class="analysis-category-footer">
                <span>
                    ${
                        reasons.length
                            ? escapeHtml(
                                reasons[0],
                            )
                            : 'View portfolio analysis'
                    }
                </span>

                <span class="analysis-chevron">
                    ›
                </span>
            </div>
        </button>
    `;
}

async function refreshAnalysis() {
    renderDashboardLoading();

    try {
        const response =
            await authenticatedFetch(
                '/api/mobile/dashboard',
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.message ||
                'Unable to refresh analysis.',
            );
        }

        currentDashboard =
            data;

        renderAnalysis();
    } catch (error) {
        renderDashboardError(
            error?.message ||
            'Unable to refresh analysis.',
        );
    }
}

function openCategoryByKey(key) {
    const category =
        getDashboardCategories()
            .find(
                (item) =>
                    String(item.key) ===
                    String(key),
            );

    if (!category) {
        renderAnalysis();

        return;
    }

    renderCategoryDetail(
        category,
    );
}


/*
|--------------------------------------------------------------------------
| Category Detail
|--------------------------------------------------------------------------
*/

function renderCategoryDetail(category) {
    const score =
        normalizeScore(
            category?.score,
        );

    const reasons =
        Array.isArray(
            category?.reasons,
        )
            ? category.reasons
            : [];

    const recommendations =
        Array.isArray(
            category?.recommendations,
        )
            ? category.recommendations
            : [];

    const categoryName =
        category?.name ||
        String(
            category?.key ||
            'Analysis',
        )
            .replaceAll('_', ' ')
            .replace(
                /\b\w/g,
                (letter) =>
                    letter.toUpperCase(),
            );

    app.innerHTML = `
        <main class="mobile-shell category-detail-shell">
            <header class="detail-header">
                <button
                    type="button"
                    class="back-button"
                    data-action="back-analysis"
                    aria-label="Back to Analysis"
                >
                    ‹
                </button>

                <div>
                    <p class="eyebrow">
                        PORTFOLIO ANALYSIS
                    </p>

                    <h1>
                        ${escapeHtml(
                            categoryName,
                        )}
                    </h1>
                </div>
            </header>

            <section class="category-detail-score-card">
                <div>
                    <p class="summary-label">
                        CATEGORY SCORE
                    </p>

                    <div class="detail-score">
                        <strong
                            class="${scoreClass(score)}"
                        >
                            ${
                                score === null
                                    ? '—'
                                    : Math.round(
                                        score,
                                    )
                            }
                        </strong>

                        <span>
                            / 100
                        </span>
                    </div>
                </div>

                <div
                    class="
                        detail-score-status
                        ${scoreClass(score)}
                    "
                >
                    ${escapeHtml(
                        category?.label ||
                        'Insufficient data',
                    )}
                </div>
            </section>

            <section class="detail-section">
                <p class="eyebrow">
                    WHAT HELMIO FOUND
                </p>

                <div class="detail-card">
                    ${
                        reasons.length
                            ? reasons
                                .map(
                                    (reason) => `
                                        <div
                                            class="
                                                detail-list-item
                                            "
                                        >
                                            <span
                                                class="
                                                    detail-marker
                                                "
                                            >
                                                !
                                            </span>

                                            <p>
                                                ${escapeHtml(
                                                    reason,
                                                )}
                                            </p>
                                        </div>
                                    `,
                                )
                                .join('')
                            : `
                                <p class="detail-empty-text">
                                    No specific findings
                                    are available for this
                                    category yet.
                                </p>
                            `
                    }
                </div>
            </section>

            <section class="detail-section">
                <p class="eyebrow">
                    WHAT TO REVIEW
                </p>

                <div class="detail-card">
                    ${
                        recommendations.length
                            ? recommendations
                                .map(
                                    (
                                        recommendation,
                                    ) => `
                                        <div
                                            class="
                                                detail-list-item
                                                recommendation
                                            "
                                        >
                                            <span
                                                class="
                                                    detail-marker
                                                "
                                            >
                                                ✓
                                            </span>

                                            <p>
                                                ${escapeHtml(
                                                    recommendation,
                                                )}
                                            </p>
                                        </div>
                                    `,
                                )
                                .join('')
                            : `
                                <p class="detail-empty-text">
                                    Helmio does not have
                                    an additional recommendation
                                    for this category.
                                </p>
                            `
                    }
                </div>
            </section>

            <button
                type="button"
                class="secondary-full-button"
                data-action="back-analysis"
            >
                Back to Analysis
            </button>

            ${renderBottomNavigation('analysis')}
        </main>
    `;

    document
        .querySelectorAll(
            '[data-action="back-analysis"]',
        )
        .forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    renderAnalysis,
                );
            },
        );

    bindBottomNavigation();
}



/*
|--------------------------------------------------------------------------
| Native Ask Helmio
|--------------------------------------------------------------------------
*/

function sleep(milliseconds) {
    return new Promise(
        (resolve) =>
            window.setTimeout(
                resolve,
                milliseconds,
            ),
    );
}

function normalizeAskConversations(data) {
    return Array.isArray(
        data?.conversations,
    )
        ? data.conversations
        : [];
}

function currentAskMessages() {
    return Array.isArray(
        currentAskConversation?.messages,
    )
        ? currentAskConversation.messages
        : [];
}

function renderAskLoading() {
    askPollGeneration += 1;

    app.innerHTML = `
        <main class="mobile-shell ask-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="ask-loading">
                <div class="loading-ring"></div>

                <strong>
                    Opening Ask Helmio
                </strong>

                <p>
                    Loading your portfolio conversations...
                </p>
            </section>

            ${renderBottomNavigation('ask')}
        </main>
    `;

    bindBottomNavigation();
}

async function loadAskHelmio(
    conversationId = null,
) {
    renderAskLoading();

    try {
        const response =
            await authenticatedFetch(
                '/api/mobile/ask-helmio',
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.message ||
                `Unable to load Ask Helmio. Server returned ${response.status}.`,
            );
        }

        currentAskConversations =
            normalizeAskConversations(
                data,
            );

        if (conversationId !== null) {
            currentAskConversation =
                currentAskConversations.find(
                    (conversation) =>
                        String(
                            conversation?.id,
                        ) ===
                        String(
                            conversationId,
                        ),
                ) || null;
        } else {
            currentAskConversation =
                currentAskConversations[0] ||
                null;
        }

        renderAskHelmio();
    } catch (error) {
        renderAskError(
            error?.message ||
            'Unable to load Ask Helmio.',
        );
    }
}

function renderAskError(message) {
    app.innerHTML = `
        <main class="mobile-shell ask-shell">
            <header class="mobile-header">
                ${renderBrand()}
            </header>

            <section class="ask-error-card">
                <span class="ask-orb">
                    ✦
                </span>

                <p class="eyebrow">
                    ASK HELMIO
                </p>

                <h1>
                    We couldn't open your conversations.
                </h1>

                <p>
                    ${escapeHtml(message)}
                </p>

                <button
                    type="button"
                    class="primary-button"
                    data-action="retry-ask"
                >
                    Try Again
                </button>
            </section>

            ${renderBottomNavigation('ask')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="retry-ask"]',
        )
        ?.addEventListener(
            'click',
            () => loadAskHelmio(),
        );

    bindBottomNavigation();
}

function renderAskConversationTabs() {
    if (!currentAskConversations.length) {
        return '';
    }

    return `
        <div class="ask-conversation-strip">
            ${currentAskConversations
                .slice(0, 8)
                .map(
                    (conversation) => {
                        const active =
                            String(
                                conversation?.id,
                            ) ===
                            String(
                                currentAskConversation?.id,
                            );

                        return `
                            <button
                                type="button"
                                class="
                                    ask-conversation-chip
                                    ${active ? 'active' : ''}
                                "
                                data-ask-conversation="${escapeHtml(
                                    conversation?.id,
                                )}"
                            >
                                ${escapeHtml(
                                    conversation?.title ||
                                    'Conversation',
                                )}
                            </button>
                        `;
                    },
                )
                .join('')}
        </div>
    `;
}

function renderAskQuickPrompts() {
    const prompts = [
        'What should I review first?',
        'Are my fees too high?',
        'How diversified am I?',
    ];

    return `
        <section class="ask-starter-card">
            <div class="ask-starter-icon">
                ✦
            </div>

            <h2>
                Ask about your portfolio
            </h2>

            <p>
                Helmio answers using the portfolio data
                and analysis available in your account.
            </p>

            <div class="ask-prompt-list">
                ${prompts
                    .map(
                        (prompt) => `
                            <button
                                type="button"
                                class="ask-prompt-button"
                                data-ask-prompt="${escapeHtml(
                                    prompt,
                                )}"
                            >
                                <span>
                                    ${escapeHtml(prompt)}
                                </span>

                                <span>
                                    ›
                                </span>
                            </button>
                        `,
                    )
                    .join('')}
            </div>
        </section>
    `;
}

function renderAskMessage(message) {
    const role =
        String(
            message?.role || '',
        ).toLowerCase();

    const assistant =
        role === 'assistant';

    const confidence =
        assistant &&
        message?.confidence
            ? `
                <span class="ask-confidence">
                    ${escapeHtml(
                        String(
                            message.confidence,
                        ).toUpperCase(),
                    )} CONFIDENCE
                </span>
            `
            : '';

    return `
        <article
            class="
                ask-message
                ${
                    assistant
                        ? 'ask-message-assistant'
                        : 'ask-message-user'
                }
            "
        >
            ${
                assistant
                    ? `
                        <div class="ask-message-avatar">
                            ✦
                        </div>
                    `
                    : ''
            }

            <div class="ask-message-content">
                ${
                    assistant
                        ? `
                            <div class="ask-message-meta">
                                <strong>
                                    Ask Helmio
                                </strong>

                                ${confidence}
                            </div>
                        `
                        : ''
                }

                <p>
                    ${escapeHtml(
                        message?.content || '',
                    )}
                </p>
            </div>
        </article>
    `;
}

function renderAskGenerating() {
    return `
        <article
            class="
                ask-message
                ask-message-assistant
                ask-message-generating
            "
        >
            <div class="ask-message-avatar">
                ✦
            </div>

            <div class="ask-message-content">
                <div class="ask-message-meta">
                    <strong>
                        Ask Helmio
                    </strong>
                </div>

                <div class="ask-thinking">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <small>
                    Reviewing your Helmio data...
                </small>
            </div>
        </article>
    `;
}

function renderAskHelmio(
    options = {},
) {
    const generating =
        Boolean(
            options.generating,
        );

    const errorMessage =
        options.errorMessage || '';

    const messages =
        currentAskMessages();

    app.innerHTML = `
        <main class="mobile-shell ask-shell">
            <header class="mobile-header">
                ${renderBrand()}

                <button
                    type="button"
                    class="header-icon-button"
                    data-action="new-ask-chat"
                    aria-label="New conversation"
                >
                    +
                </button>
            </header>

            <section class="ask-heading">
                <div class="ask-heading-row">
                    <div>
                        <p class="eyebrow">
                            AI PORTFOLIO ASSISTANT
                        </p>

                        <h1>
                            Ask Helmio
                        </h1>
                    </div>

                    <span class="ask-orb">
                        ✦
                    </span>
                </div>

                <p>
                    Ask questions about the costs, risk,
                    performance, diversification and activity
                    Helmio sees in your portfolio.
                </p>
            </section>

            ${renderAskConversationTabs()}

            <section
                class="
                    ask-chat
                    ${messages.length ? 'has-messages' : ''}
                "
                id="ask-chat"
            >
                ${
                    messages.length
                        ? messages
                            .map(
                                renderAskMessage,
                            )
                            .join('')
                        : renderAskQuickPrompts()
                }

                ${
                    generating
                        ? renderAskGenerating()
                        : ''
                }

                ${
                    errorMessage
                        ? `
                            <div class="ask-inline-error">
                                ${escapeHtml(
                                    errorMessage,
                                )}
                            </div>
                        `
                        : ''
                }
            </section>

            <form
                id="ask-form"
                class="ask-composer"
            >
                <textarea
                    id="ask-input"
                    name="question"
                    rows="1"
                    maxlength="2000"
                    placeholder="Ask about your portfolio..."
                    aria-label="Ask Helmio a question"
                    ${generating ? 'disabled' : ''}
                ></textarea>

                <button
                    type="submit"
                    class="ask-send-button"
                    aria-label="Send question"
                    ${generating ? 'disabled' : ''}
                >
                    ↑
                </button>
            </form>

            ${renderBottomNavigation('ask')}
        </main>
    `;

    document
        .querySelector(
            '[data-action="new-ask-chat"]',
        )
        ?.addEventListener(
            'click',
            () => {
                askPollGeneration += 1;
                currentAskConversation = null;
                renderAskHelmio();
            },
        );

    document
        .querySelectorAll(
            '[data-ask-conversation]',
        )
        .forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    () => {
                        askPollGeneration += 1;

                        currentAskConversation =
                            currentAskConversations.find(
                                (conversation) =>
                                    String(
                                        conversation?.id,
                                    ) ===
                                    String(
                                        button.dataset
                                            .askConversation,
                                    ),
                            ) || null;

                        renderAskHelmio();
                    },
                );
            },
        );

    document
        .querySelectorAll(
            '[data-ask-prompt]',
        )
        .forEach(
            (button) => {
                button.addEventListener(
                    'click',
                    () => {
                        const input =
                            document.querySelector(
                                '#ask-input',
                            );

                        if (!input) {
                            return;
                        }

                        input.value =
                            button.dataset
                                .askPrompt ||
                            '';

                        input.focus();
                    },
                );
            },
        );

    document
        .querySelector(
            '#ask-form',
        )
        ?.addEventListener(
            'submit',
            handleAskSubmit,
        );

    const textarea =
        document.querySelector(
            '#ask-input',
        );

    textarea
        ?.addEventListener(
            'input',
            () => {
                textarea.style.height =
                    'auto';

                textarea.style.height =
                    `${Math.min(
                        textarea.scrollHeight,
                        132,
                    )}px`;
            },
        );

    bindBottomNavigation();

    window.requestAnimationFrame(
        scrollAskToBottom,
    );
}

function scrollAskToBottom() {
    const chat =
        document.querySelector(
            '#ask-chat',
        );

    if (!chat) {
        return;
    }

    chat.scrollTop =
        chat.scrollHeight;

    window.scrollTo({
        top:
            document.body.scrollHeight,
        behavior:
            'smooth',
    });
}

async function handleAskSubmit(event) {
    event.preventDefault();

    const form =
        event.currentTarget;

    const formData =
        new FormData(form);

    const question =
        String(
            formData.get('question') ||
            '',
        ).trim();

    if (!question) {
        return;
    }

    const temporaryUserMessage = {
        id:
            `local-${Date.now()}`,
        role:
            'user',
        content:
            question,
        status:
            'completed',
    };

    if (!currentAskConversation) {
        currentAskConversation = {
            id: null,
            title: question,
            status: 'active',
            messages: [],
        };
    }

    if (
        !Array.isArray(
            currentAskConversation.messages,
        )
    ) {
        currentAskConversation.messages = [];
    }

    currentAskConversation.messages.push(
        temporaryUserMessage,
    );

    renderAskHelmio({
        generating: true,
    });

    try {
        const response =
            await authenticatedFetch(
                '/api/mobile/ask-helmio',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json',
                    },

                    body:
                        JSON.stringify({
                            question,

                            conversation_id:
                                currentAskConversation
                                    ?.id ||
                                null,
                        }),
                },
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.errors
                    ?.question?.[0] ||
                data?.message ||
                `Unable to send question. Server returned ${response.status}.`,
            );
        }

        const conversationId =
            data?.conversation_id;

        const questionMessageId =
            data?.question_message_id;

        if (
            !conversationId ||
            !questionMessageId
        ) {
            throw new Error(
                'Helmio did not return the conversation information needed to retrieve the answer.',
            );
        }

        currentAskConversation.id =
            conversationId;

        const messageIndex =
            currentAskConversation
                .messages
                .findIndex(
                    (message) =>
                        message.id ===
                        temporaryUserMessage.id,
                );

        if (messageIndex >= 0) {
            currentAskConversation
                .messages[
                    messageIndex
                ] = {
                    ...temporaryUserMessage,
                    ...(data?.message || {}),
                };
        }

        const existingIndex =
            currentAskConversations.findIndex(
                (conversation) =>
                    String(
                        conversation?.id,
                    ) ===
                    String(
                        conversationId,
                    ),
            );

        if (existingIndex >= 0) {
            currentAskConversations[
                existingIndex
            ] =
                currentAskConversation;
        } else {
            currentAskConversations.unshift(
                currentAskConversation,
            );
        }

        renderAskHelmio({
            generating: true,
        });

        await pollAskHelmioResponse(
            conversationId,
            questionMessageId,
        );
    } catch (error) {
        renderAskHelmio({
            errorMessage:
                error?.message ||
                'Unable to send your question.',
        });
    }
}

async function pollAskHelmioResponse(
    conversationId,
    questionMessageId,
) {
    const generation =
        ++askPollGeneration;

    const maxAttempts =
        60;

    for (
        let attempt = 0;
        attempt < maxAttempts;
        attempt += 1
    ) {
        if (
            generation !==
            askPollGeneration
        ) {
            return;
        }

        try {
            const response =
                await authenticatedFetch(
                    `/api/mobile/ask-helmio/${encodeURIComponent(
                        conversationId,
                    )}/status?question_message_id=${encodeURIComponent(
                        questionMessageId,
                    )}`,
                );

            const data =
                await parseResponse(
                    response,
                );

            if (!response.ok) {
                throw new Error(
                    data?.message ||
                    `Unable to retrieve Ask Helmio response. Server returned ${response.status}.`,
                );
            }

            if (
                data?.finished &&
                data?.assistant_message
            ) {
                const assistantMessage =
                    data.assistant_message;

                const messages =
                    currentAskMessages();

                const alreadyExists =
                    messages.some(
                        (message) =>
                            String(
                                message?.id,
                            ) ===
                            String(
                                assistantMessage?.id,
                            ),
                    );

                if (!alreadyExists) {
                    messages.push(
                        assistantMessage,
                    );
                }

                const conversationIndex =
                    currentAskConversations.findIndex(
                        (conversation) =>
                            String(
                                conversation?.id,
                            ) ===
                            String(
                                conversationId,
                            ),
                    );

                if (
                    conversationIndex >= 0
                ) {
                    currentAskConversations[
                        conversationIndex
                    ] =
                        currentAskConversation;
                }

                renderAskHelmio();

                return;
            }
        } catch (error) {
            if (
                generation !==
                askPollGeneration
            ) {
                return;
            }

            renderAskHelmio({
                errorMessage:
                    error?.message ||
                    'Unable to retrieve the response.',
            });

            return;
        }

        await sleep(2000);
    }

    if (
        generation !==
        askPollGeneration
    ) {
        return;
    }

    renderAskHelmio({
        errorMessage:
            'Helmio is still working on that answer. Open this conversation again in a moment to refresh it.',
    });
}

/*
|--------------------------------------------------------------------------
| Bottom Navigation
|--------------------------------------------------------------------------
*/

function renderBottomNavigation(
    active = 'home',
) {
    const navClass =
        (name) =>
            name === active
                ? 'bottom-nav-item active'
                : 'bottom-nav-item';

    return `
        <nav class="bottom-navigation">
            <button
                type="button"
                class="${navClass('home')}"
                data-nav="home"
            >
                <span class="bottom-nav-icon">
                    ♢
                </span>

                <span>
                    Home
                </span>
            </button>

            <button
                type="button"
                class="${navClass('analysis')}"
                data-nav="analysis"
            >
                <span class="bottom-nav-icon">
                    Ⅱ
                </span>

                <span>
                    Analysis
                </span>
            </button>

            <button
                type="button"
                class="${navClass('ask')} bottom-nav-primary"
                data-nav="ask"
            >
                <span class="bottom-nav-primary-icon">
                    ✦
                </span>

                <span>
                    Ask
                </span>
            </button>

            <button
                type="button"
                class="${navClass('accounts')}"
                data-nav="accounts"
            >
                <span class="bottom-nav-icon">
                    ◎
                </span>

                <span>
                    Accounts
                </span>
            </button>

            <button
                type="button"
                class="${navClass('profile')}"
                data-nav="profile"
            >
                <span class="bottom-nav-icon">
                    ○
                </span>

                <span>
                    Profile
                </span>
            </button>
        </nav>
    `;
}

function bindBottomNavigation() {
    document
        .querySelector(
            '[data-nav="home"]',
        )
        ?.addEventListener(
            'click',
            () => {
                if (currentDashboard) {
                    if (
                        currentDashboard.status ===
                        'no_accounts'
                    ) {
                        renderNoAccounts(
                            currentDashboard,
                        );
                    } else {
                        renderDashboard(
                            currentDashboard,
                        );
                    }

                    return;
                }

                loadDashboard();
            },
        );

    document
        .querySelector(
            '[data-nav="analysis"]',
        )
        ?.addEventListener(
            'click',
            renderAnalysis,
        );

    document
        .querySelector(
            '[data-nav="ask"]',
        )
        ?.addEventListener(
            'click',
            openAskHelmio,
        );

    document
        .querySelector(
            '[data-nav="accounts"]',
        )
        ?.addEventListener(
            'click',
            openAccounts,
        );

    document
        .querySelector(
            '[data-nav="profile"]',
        )
        ?.addEventListener(
            'click',
            openProfile,
        );
}


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

async function logout() {
    try {
        const token =
            await getToken();

        if (token) {
            await fetch(
                `${API_URL}/api/mobile/logout`,
                {
                    method: 'POST',

                    headers: {
                        Accept:
                            'application/json',

                        Authorization:
                            `Bearer ${token}`,
                    },
                },
            );
        }
    } catch {
        // We still remove the local token
        // if the server logout request fails.
    }

    await removeToken();

    currentDashboard = null;
    currentAskConversation = null;
    currentAskConversations = [];
    askPollGeneration += 1;

    currentAccounts = [];
    currentAccountsSummary = null;
    currentAccountDetail = null;
    currentProfile = null;
    currentProfileAccountsSummary = null;

    renderLogin();
}


/*
|--------------------------------------------------------------------------
| Existing Session
|--------------------------------------------------------------------------
*/

async function bootAuthenticatedUser() {
    const token =
        await getToken();

    if (!token) {
        renderWelcome();

        return;
    }

    renderDashboardLoading();

    try {
        const response =
            await authenticatedFetch(
                '/api/mobile/me',
            );

        const data =
            await parseResponse(
                response,
            );

        if (!response.ok) {
            throw new Error(
                data?.message ||
                'Unable to restore your session.',
            );
        }

        if (
            data?.subscription?.active
        ) {
            await loadDashboard();

            return;
        }

        renderSubscriptionRequired(
            data.user,
        );
    } catch (error) {
        const stillHasToken =
            await getToken();

        if (!stillHasToken) {
            return;
        }

        renderDashboardError(
            error?.message ||
            'Unable to restore your Helmio session.',
        );
    }
}


/*
|--------------------------------------------------------------------------
| Boot
|--------------------------------------------------------------------------
*/

bootAuthenticatedUser();