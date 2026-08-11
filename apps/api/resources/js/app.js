import Alpine from 'alpinejs';

import './pwa-install';
import './push-notifications';

window.Alpine = Alpine;

Alpine.start();

/*
 * Register Helmio's root-scoped service worker.
 */
async function registerHelmioServiceWorker() {
    if (
        ! ('serviceWorker' in navigator)
    ) {
        console.warn(
            'Service workers are not supported by this browser.',
        );

        return;
    }

    try {
        const registration =
            await navigator
                .serviceWorker
                .register(
                    '/sw.js',
                    {
                        scope: '/',
                        type: 'module',
                    },
                );

        window.setInterval(
            () => {
                registration
                    .update()
                    .catch(
                        error => {
                            console.error(
                                'Unable to check for Helmio service worker update:',
                                error,
                            );
                        },
                    );
            },
            60 * 60 * 1000,
        );

        window.dispatchEvent(
            new CustomEvent(
                'helmio:service-worker-ready',
                {
                    detail: {
                        registration,
                    },
                },
            ),
        );

        window.HelmioPwa = {
            registration,

            async update() {
                return registration
                    .update();
            },

            async syncBadge() {
                return syncHelmioBadge();
            },
        };

        /*
         * Synchronize the badge as soon as Helmio loads.
         */
        await syncHelmioBadge();

        console.info(
            'Helmio service worker registered:',
            registration.scope,
        );
    } catch (error) {
        console.error(
            'Helmio service worker registration failed:',
            error,
        );
    }
}

/*
 * Ask Laravel for the authoritative unread count,
 * then update the installed Helmio app badge.
 */
async function syncHelmioBadge() {
    if (
        ! (
            'setAppBadge'
            in navigator
        )
    ) {
        return;
    }

    try {
        const response =
            await fetch(
                '/notifications/unread-count',
                {
                    method:
                        'GET',

                    credentials:
                        'same-origin',

                    headers: {
                        Accept:
                            'application/json',
                    },

                    cache:
                        'no-store',
                },
            );

        /*
         * The user may be logged out or the endpoint
         * may be unavailable during onboarding.
         */
        if (! response.ok) {
            return;
        }

        const data =
            await response.json();

        const unreadCount =
            Number(
                data.unread_count
                ?? 0,
            );

        if (unreadCount > 0) {
            await navigator
                .setAppBadge(
                    unreadCount,
                );

            return;
        }

        if (
            'clearAppBadge'
            in navigator
        ) {
            await navigator
                .clearAppBadge();

            return;
        }

        await navigator
            .setAppBadge(0);
    } catch (error) {
        console.error(
            'Unable to synchronize Helmio badge:',
            error,
        );
    }
}

/*
 * A real new Helmio notification arrived.
 *
 * Refresh the open page so dashboard data, the
 * notification bell, and unread count are current.
 */
if (
    'serviceWorker'
    in navigator
) {
    navigator.serviceWorker
        .addEventListener(
            'message',
            event => {
                if (
                    event.data?.type
                    !==
                    'HELMIO_NOTIFICATION_RECEIVED'
                ) {
                    return;
                }

                /*
                 * A silent/badge-sync push is no longer
                 * used. Real notification pushes can
                 * refresh the active Helmio page.
                 */
                window.location.reload();
            },
        );
}

/*
 * When a user returns to Helmio on their phone,
 * synchronize the badge with the database.
 *
 * This catches notifications that were removed,
 * read, or cleared from another device/browser.
 */
document.addEventListener(
    'visibilitychange',
    () => {
        if (
            document.visibilityState
            === 'visible'
        ) {
            syncHelmioBadge();
        }
    },
);

/*
 * Desktop/browser window becomes active again.
 */
window.addEventListener(
    'focus',
    () => {
        syncHelmioBadge();
    },
);

/*
 * Handles returning from Safari's back-forward cache
 * and reopening the installed PWA.
 */
window.addEventListener(
    'pageshow',
    () => {
        syncHelmioBadge();
    },
);

registerHelmioServiceWorker();