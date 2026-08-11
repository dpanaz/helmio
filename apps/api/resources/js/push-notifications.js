function urlBase64ToUint8Array(base64String) {
    const padding =
        '='.repeat(
            (4 - base64String.length % 4) % 4,
        );

    const base64 =
        (
            base64String
            + padding
        )
            .replace(/-/g, '+')
            .replace(/_/g, '/');

    const rawData =
        window.atob(base64);

    return Uint8Array.from(
        [...rawData].map(
            character =>
                character.charCodeAt(0),
        ),
    );
}

async function getCsrfToken() {
    return document
        .querySelector(
            'meta[name="csrf-token"]',
        )
        ?.getAttribute('content');
}

async function getRegistration() {
    if (
        ! ('serviceWorker' in navigator)
    ) {
        throw new Error(
            'Service workers are not supported on this device.',
        );
    }

    return navigator.serviceWorker.ready;
}

async function getVapidPublicKey() {
    const response =
        await fetch(
            '/push-subscriptions/vapid-public-key',
            {
                headers: {
                    Accept:
                        'application/json',
                },
            },
        );

    if (! response.ok) {
        throw new Error(
            'Unable to load the Helmio push key.',
        );
    }

    const data =
        await response.json();

    if (! data.public_key) {
        throw new Error(
            'Helmio push notifications are not configured.',
        );
    }

    return data.public_key;
}

async function saveSubscription(
    subscription,
) {
    const csrfToken =
        await getCsrfToken();

    const json =
        subscription.toJSON();

    const response =
        await fetch(
            '/push-subscriptions',
            {
                method: 'POST',

                headers: {
                    Accept:
                        'application/json',

                    'Content-Type':
                        'application/json',

                    'X-CSRF-TOKEN':
                        csrfToken,
                },

                body:
                    JSON.stringify({
                        endpoint:
                            json.endpoint,

                        keys: {
                            p256dh:
                                json.keys
                                    ?.p256dh,

                            auth:
                                json.keys
                                    ?.auth,
                        },

                        content_encoding:
                            'aes128gcm',
                    }),
            },
        );

    if (! response.ok) {
        const body =
            await response.text();

        throw new Error(
            body
            || 'Unable to save the push subscription.',
        );
    }

    return response.json();
}

async function removeSubscription(
    subscription,
) {
    const csrfToken =
        await getCsrfToken();

    const response =
        await fetch(
            '/push-subscriptions',
            {
                method: 'DELETE',

                headers: {
                    Accept:
                        'application/json',

                    'Content-Type':
                        'application/json',

                    'X-CSRF-TOKEN':
                        csrfToken,
                },

                body:
                    JSON.stringify({
                        endpoint:
                            subscription.endpoint,
                    }),
            },
        );

    if (! response.ok) {
        throw new Error(
            'Unable to remove the Helmio push subscription.',
        );
    }
}

async function subscribe() {
    if (
        ! ('Notification' in window)
        || ! ('PushManager' in window)
    ) {
        throw new Error(
            'Push notifications are not supported on this device.',
        );
    }

    const permission =
        await Notification
            .requestPermission();

    if (permission !== 'granted') {
        throw new Error(
            'Notification permission was not granted.',
        );
    }

    const registration =
        await getRegistration();

    let subscription =
        await registration
            .pushManager
            .getSubscription();

    if (! subscription) {
        const publicKey =
            await getVapidPublicKey();

        subscription =
            await registration
                .pushManager
                .subscribe({
                    userVisibleOnly:
                        true,

                    applicationServerKey:
                        urlBase64ToUint8Array(
                            publicKey,
                        ),
                });
    }

    await saveSubscription(
        subscription,
    );

    return subscription;
}

async function unsubscribe() {
    const registration =
        await getRegistration();

    const subscription =
        await registration
            .pushManager
            .getSubscription();

    if (! subscription) {
        return;
    }

    await removeSubscription(
        subscription,
    );

    await subscription.unsubscribe();
}

async function isSubscribed() {
    if (
        ! ('serviceWorker' in navigator)
        || ! ('PushManager' in window)
    ) {
        return false;
    }

    const registration =
        await getRegistration();

    return Boolean(
        await registration
            .pushManager
            .getSubscription(),
    );
}

window.HelmioPush = {
    subscribe,
    unsubscribe,
    isSubscribed,
};