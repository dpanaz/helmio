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
 * Vite builds Helmio assets under /build/, while this
 * service worker is served from /sw.js so it can control
 * the entire application.
 *
 * Rewrite injected precache URLs so Workbox fetches
 * /build/assets/... instead of /assets/....
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
 * Receive a Web Push payload from Helmio.
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
                self.registration
                    .showNotification(
                        title,
                        options,
                    ),

                updateAppBadge(
                    unreadCount,
                ),
            ]),
        );
    },
);

/*
 * Open or focus Helmio when a notification is tapped.
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
 * Allow the open Helmio application to synchronize
 * the Home Screen badge with the unread count.
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

/*
 * Update the installed Helmio app badge.
 */
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
        }
    } catch (error) {
        console.error(
            'Unable to update Helmio badge:',
            error,
        );
    }
}

/*
 * Focus an existing Helmio window when possible.
 * Otherwise open a new one.
 */
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