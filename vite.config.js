import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import mkcert from 'vite-plugin-mkcert'

export default defineConfig({
    "window.global": {},
    server: {
        https: true,
        host: 'localhost',
    },
    plugins: [
        mkcert(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // Laravel Viteプラグインとの連携のため
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js', // @ で resources/js ディレクトリを参照できるように設定
        },
    }
});
