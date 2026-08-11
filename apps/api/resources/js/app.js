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
        };

        /*
         * Synchronize the app icon badge whenever
         * the current page exposes an unread count.
         */
        await syncHelmioBadge(
            registration,
        );

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
 * New push received while Helmio is already open.
 *
 * Reload the page so:
 *
 * - bell count updates
 * - dashboard alerts update
 * - notification center updates
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

                window.location.reload();
            },
        );
}

/*
 * Synchronize Home Screen badge using the unread
 * count supplied by the current Blade page.
 */
async function syncHelmioBadge(
    registration,
) {
    const badgeElement =
        document.querySelector(
            '[data-helmio-unread-count]',
        );

    if (! badgeElement) {
        return;
    }

    const unreadCount =
        Number(
            badgeElement.dataset
                .helmioUnreadCount
            ?? 0,
        );

    const worker =
        registration.active
        ?? registration.waiting
        ?? registration.installing;

    if (! worker) {
        return;
    }

    if (unreadCount > 0) {
        worker.postMessage({
            type:
                'HELMIO_SET_BADGE',

            count:
                unreadCount,
        });

        return;
    }

    worker.postMessage({
        type:
            'HELMIO_CLEAR_BADGE',
    });
}

registerHelmioServiceWorker();