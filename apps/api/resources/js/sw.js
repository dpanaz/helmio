import {
    cleanupOutdatedCaches,
    precacheAndRoute,
} from 'workbox-precaching';

import {
    clientsClaim,
} from 'workbox-core';

self.skipWaiting();

clientsClaim();

cleanupOutdatedCaches();

/*
 * Vite builds Helmio assets beneath /build/,
 * while this service worker is served from /sw.js.
 *
 * Rewrite injected Vite asset URLs so Workbox
 * requests them from the correct location.
 */
const precacheManifest =
    self.__WB_MANIFEST.map(
        entry => {
            if (
                typeof entry.url === 'string'
                && entry.url.startsWith('assets/')
            ) {
                return {
                    ...entry,
                    url:
                        `/build/${entry.url}`,
                };
            }

            return entry;
        },
    );

precacheAndRoute(
    precacheManifest,
);

/*
 * Receive a Web Push notification.
 */
self.addEventListener(
    'push',
    event => {
        let payload = {};

        if (event.data) {
            try {
                payload =
                    event.data.json();
            } catch {
                payload = {
                    body:
                        event.data.text(),
                };
            }
        }

        const title =
            payload.title
            ?? 'Helmio';

        const body =
            payload.body
            ?? payload.message
            ?? 'You have a new Helmio notification.';

        const actionUrl =
            payload.action_url
            ?? '/notifications';

        const unreadCount =
            Number(
                payload.unread_count
                ?? 1,
            );

        const options = {
            body,

            icon:
                '/icons/icon-192.png',

            badge:
                '/icons/icon-192.png',

            data: {
                actionUrl,
            },

            tag:
                payload.tag
                ?? payload.event_key
                ?? undefined,

            renotify:
                false,
        };

        event.waitUntil(
            Promise.all([
                /*
                 * Show the operating-system notification.
                 */
                self.registration
                    .showNotification(
                        title,
                        options,
                    ),

                /*
                 * Synchronize the Home Screen badge.
                 */
                updateAppBadge(
                    unreadCount,
                ),

                /*
                 * Tell any open Helmio windows that
                 * new notification data is available.
                 */
                notifyOpenClients(
                    payload,
                ),
            ]),
        );
    },
);

/*
 * Open/focus Helmio when the push is tapped.
 */
self.addEventListener(
    'notificationclick',
    event => {
        event.notification.close();

        const actionUrl =
            event.notification
                .data
                ?.actionUrl
            ?? '/notifications';

        event.waitUntil(
            focusOrOpenWindow(
                actionUrl,
            ),
        );
    },
);

/*
 * Receive badge updates from an open Helmio page.
 *
 * This handles cases such as:
 *
 * - notification marked read
 * - notification removed
 * - all notifications marked read
 * - notification center refreshed
 */
self.addEventListener(
    'message',
    event => {
        const data =
            event.data
            ?? {};

        if (
            data.type
            === 'HELMIO_SET_BADGE'
        ) {
            event.waitUntil(
                updateAppBadge(
                    Number(
                        data.count
                        ?? 0,
                    ),
                ),
            );

            return;
        }

        if (
            data.type
            === 'HELMIO_CLEAR_BADGE'
        ) {
            event.waitUntil(
                updateAppBadge(0),
            );
        }
    },
);

async function notifyOpenClients(
    payload,
) {
    const clientList =
        await self.clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        });

    for (
        const client
        of clientList
    ) {
        client.postMessage({
            type:
                'HELMIO_NOTIFICATION_RECEIVED',

            payload,
        });
    }
}

async function updateAppBadge(
    count,
) {
    if (
        ! self.navigator
        || ! (
            'setAppBadge'
            in self.navigator
        )
    ) {
        return;
    }

    try {
        if (count > 0) {
            await self.navigator
                .setAppBadge(
                    count,
                );

            return;
        }

        if (
            'clearAppBadge'
            in self.navigator
        ) {
            await self.navigator
                .clearAppBadge();

            return;
        }

        await self.navigator
            .setAppBadge(0);
    } catch (error) {
        console.error(
            'Unable to update Helmio badge:',
            error,
        );
    }
}

async function focusOrOpenWindow(
    actionUrl,
) {
    const url =
        new URL(
            actionUrl,
            self.location.origin,
        ).href;

    const clientList =
        await self.clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        });

    /*
     * Prefer an already-open copy of the exact page.
     */
    for (
        const client
        of clientList
    ) {
        if (
            client.url === url
            && 'focus' in client
        ) {
            return client.focus();
        }
    }

    /*
     * Otherwise reuse an existing Helmio window.
     */
    for (
        const client
        of clientList
    ) {
        if (
            'focus' in client
            && client.url.startsWith(
                self.location.origin,
            )
        ) {
            await client.focus();

            if (
                'navigate'
                in client
            ) {
                return client.navigate(
                    url,
                );
            }

            return client;
        }
    }

    /*
     * Finally, open a new Helmio window.
     */
    if (
        self.clients.openWindow
    ) {
        return self.clients
            .openWindow(
                url,
            );
    }

    return null;
}