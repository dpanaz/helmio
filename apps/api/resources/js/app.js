import Alpine from 'alpinejs';
import './pwa-install';
import './push-notifications';

window.Alpine = Alpine;

Alpine.start();

async function registerHelmioServiceWorker() {
    if (! ('serviceWorker' in navigator)) {
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

        window.setInterval(
            () => {
                registration.update();
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
        };
    } catch (error) {
        console.error(
            'Helmio service worker registration failed:',
            error,
        );
    }
}

registerHelmioServiceWorker();