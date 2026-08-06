import Alpine from 'alpinejs';
import { registerSW } from 'virtual:pwa-register';
import './pwa-install';

window.Alpine = Alpine;

Alpine.start();

const updateServiceWorker = registerSW({
    immediate: true,

    onRegisteredSW(
        _serviceWorkerUrl,
        registration,
    ) {
        if (! registration) {
            return;
        }

        window.setInterval(
            () => {
                registration.update();
            },
            60 * 60 * 1000,
        );
    },

    onOfflineReady() {
        window.dispatchEvent(
            new CustomEvent(
                'helmio:pwa-offline-ready',
            ),
        );
    },

    onNeedRefresh() {
        window.dispatchEvent(
            new CustomEvent(
                'helmio:pwa-update-available',
                {
                    detail: {
                        updateServiceWorker,
                    },
                },
            ),
        );
    },

    onRegisterError(error) {
        console.error(
            'Helmio service worker registration failed:',
            error,
        );
    },
});

window.HelmioPwa = {
    updateServiceWorker,
};