let deferredInstallPrompt = null;

const dismissalKey =
    'helmio-pwa-install-dismissed-at';

const dismissalDays = 7;

function isStandalone() {
    return (
        window.matchMedia(
            '(display-mode: standalone)',
        ).matches
        || window.navigator.standalone === true
    );
}

function isIos() {
    return /iphone|ipad|ipod/i.test(
        window.navigator.userAgent,
    );
}

function recentlyDismissed() {
    const dismissedAt = Number(
        window.localStorage.getItem(
            dismissalKey,
        ),
    );

    if (! dismissedAt) {
        return false;
    }

    const dismissalPeriod =
        dismissalDays
        * 24
        * 60
        * 60
        * 1000;

    return (
        Date.now() - dismissedAt
        < dismissalPeriod
    );
}

function showInstallPrompt(type) {
    if (
        isStandalone()
        || recentlyDismissed()
    ) {
        return;
    }

    window.dispatchEvent(
        new CustomEvent(
            'helmio:pwa-install-available',
            {
                detail: {
                    type,
                },
            },
        ),
    );
}

window.addEventListener(
    'beforeinstallprompt',
    (event) => {
        event.preventDefault();

        deferredInstallPrompt = event;

        showInstallPrompt('native');
    },
);

window.addEventListener(
    'appinstalled',
    () => {
        deferredInstallPrompt = null;

        window.dispatchEvent(
            new CustomEvent(
                'helmio:pwa-installed',
            ),
        );
    },
);

window.HelmioInstallPwa =
    async function () {
        if (! deferredInstallPrompt) {
            return false;
        }

        await deferredInstallPrompt.prompt();

        const result =
            await deferredInstallPrompt
                .userChoice;

        deferredInstallPrompt = null;

        return (
            result.outcome === 'accepted'
        );
    };

window.HelmioDismissPwaInstall =
    function () {
        window.localStorage.setItem(
            dismissalKey,
            Date.now().toString(),
        );
    };

document.addEventListener(
    'DOMContentLoaded',
    () => {
        if (
            isIos()
            && ! isStandalone()
        ) {
            showInstallPrompt('ios');
        }
    },
);