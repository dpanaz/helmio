import Alpine from 'alpinejs';

import './pwa-install';
import './push-notifications';

window.Alpine = Alpine;

Alpine.start();

let lastNotificationState = null;
let notificationPollTimer = null;

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

        window.HelmioPwa = {
            registration,

            async update() {
                return registration.update();
            },

            async syncNotifications() {
                return syncNotificationState();
            },
        };

        await syncNotificationState({
            initialize:
                true,
        });

        startNotificationPolling();

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
 * Retrieve the authoritative notification state
 * from Laravel.
 */
async function fetchNotificationState() {
    const response =
        await fetch(
            '/notifications/state',
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

    if (! response.ok) {
        return null;
    }

    return response.json();
}

/*
 * Keep badge and notification pages synchronized.
 */
async function syncNotificationState(
    {
        initialize = false,
    } = {},
) {
    try {
        const state =
            await fetchNotificationState();

        if (! state) {
            return;
        }

        await updateAppBadge(
            Number(
                state.unread
                ?? 0,
            ),
        );

        if (
            initialize
            || lastNotificationState === null
        ) {
            lastNotificationState =
                state;

            return;
        }

        const changed =
            Number(state.total)
                !== Number(
                    lastNotificationState.total
                )
            || Number(state.unread)
                !== Number(
                    lastNotificationState.unread
                )
            || String(
                state.latest_id
                ?? ''
            )
                !== String(
                    lastNotificationState.latest_id
                    ?? ''
                );

        lastNotificationState =
            state;

        if (! changed) {
            return;
        }

        /*
         * If the user is currently looking at the
         * notification center, reload it immediately.
         */
        if (
            window.location.pathname
            === '/notifications'
        ) {
            window.location.reload();

            return;
        }

        /*
         * Other Helmio pages can refresh lightweight UI
         * elements later without forcing a full reload.
         */
        window.dispatchEvent(
            new CustomEvent(
                'helmio:notifications-changed',
                {
                    detail:
                        state,
                },
            ),
        );
    } catch (error) {
        console.error(
            'Unable to synchronize Helmio notifications:',
            error,
        );
    }
}

/*
 * Synchronize the installed app icon badge.
 */
async function updateAppBadge(
    unreadCount,
) {
    if (
        ! (
            'setAppBadge'
            in navigator
        )
    ) {
        return;
    }

    try {
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
 * Check every 10 seconds while Helmio is visible.
 */
function startNotificationPolling() {
    if (notificationPollTimer) {
        window.clearInterval(
            notificationPollTimer,
        );
    }

    notificationPollTimer =
        window.setInterval(
            () => {
                if (
                    document.visibilityState
                    !== 'visible'
                ) {
                    return;
                }

                syncNotificationState();
            },
            10 * 1000,
        );
}

/*
 * A real Web Push arrived.
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

                syncNotificationState();
            },
        );
}

/*
 * Immediately resync when the iPhone/PWA
 * returns to the foreground.
 */
document.addEventListener(
    'visibilitychange',
    () => {
        if (
            document.visibilityState
            === 'visible'
        ) {
            syncNotificationState();
        }
    },
);

window.addEventListener(
    'focus',
    () => {
        syncNotificationState();
    },
);

window.addEventListener(
    'pageshow',
    () => {
        syncNotificationState();
    },
);

registerHelmioServiceWorker();