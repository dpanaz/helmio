<div
    x-data="{
        visible: false,
        installType: null,

        init() {
            window.addEventListener(
                'helmio:pwa-install-available',
                (event) => {
                    this.installType =
                        event.detail.type;

                    this.visible = true;
                }
            );

            window.addEventListener(
                'helmio:pwa-installed',
                () => {
                    this.visible = false;
                }
            );
        },

        async install() {
            const installed =
                await window.HelmioInstallPwa?.();

            if (installed) {
                this.visible = false;
            }
        },

        dismiss() {
            window.HelmioDismissPwaInstall?.();
            this.visible = false;
        },
    }"
    x-show="visible"
    x-cloak
    class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-lg"
>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="flex items-start gap-4">
            <img
                src="{{ asset('icons/icon-192.png') }}"
                alt="Helmio"
                class="h-14 w-14 rounded-xl"
            >

            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-950">
                    Install Helmio
                </p>

                <template x-if="installType === 'native'">
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Add Helmio to this device for faster access and an app-like experience.
                    </p>
                </template>

                <template x-if="installType === 'ios'">
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        In Safari, tap the Share button, then choose “Add to Home Screen.”
                    </p>
                </template>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        x-show="installType === 'native'"
                        type="button"
                        x-on:click="install"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                    >
                        Install
                    </button>

                    <button
                        type="button"
                        x-on:click="dismiss"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Not now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>