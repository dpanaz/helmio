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

precacheAndRoute(
    self.__WB_MANIFEST,
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
 * Open the appropriate Helmio page when the
 * user taps a push notification.
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
 * Allow the open Helmio application to update
 * the Home Screen badge whenever unread counts
 * change.
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

    if (
        self.clients.openWindow
    ) {
        return self.clients
            .openWindow(url);
    }

    return null;
}