import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),

        VitePWA({
            registerType: 'autoUpdate',

            injectRegister: 'auto',

            includeAssets: [
                'favicon.ico',
                'robots.txt',
                'apple-touch-icon.png',
            ],

            manifest: {
                id: '/',

                name: 'Helmio',

                short_name: 'Helmio',

                description:
                    'Monitor your investments. Audit your advisor. Protect your future.',

                theme_color: '#0F172A',

                background_color: '#FFFFFF',

                display: 'standalone',

                orientation: 'portrait',

                scope: '/',

                start_url: '/',

                icons: [
                    {
                        src: '/icons/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },

                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },

                    {
                        src: '/icons/icon-maskable.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },

            workbox: {
                cleanupOutdatedCaches: true,

                clientsClaim: true,

                skipWaiting: true,

                globPatterns: [
                    '**/*.{js,css,html,ico,png,svg,woff2}',
                ],

                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,

                        handler: 'CacheFirst',

                        options: {
                            cacheName: 'google-fonts',
                        },
                    },
                ],
            },

            devOptions: {
                enabled: true,
            },
        }),
    ],
});