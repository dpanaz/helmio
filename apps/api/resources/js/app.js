import Alpine from 'alpinejs';

import './pwa-install';
import './push-notifications';

window.Alpine = Alpine;

Alpine.start();

/*
 * Register Helmio's service worker.
 *
 * The worker is copied to /public/sw.js during the
 * production build so that it can control the entire
 * Helmio application with scope "/".
 */
async function registerHelmioServiceWorker() {
    if (! ('serviceWorker' in navigator)) {
        console.warn(
            'Service workers are not supported by this browser.',
        );

        return;
    }

    try {
        const registration =
            await navigator.serviceWorker.register(
                '/sw.js',
                {
                    scope: '/',
                    type: 'module',
                },
            );

        /*
         * Periodically check for a newer Helmio
         * service worker.
         */
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

        /*
         * Let the rest of Helmio know that the
         * service worker has been registered.
         */
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

        /*
         * Expose the registration for debugging
         * and other frontend functionality.
         */
        window.HelmioPwa = {
            registration,

            async update() {
                return registration.update();
            },
        };

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

registerHelmioServiceWorker();