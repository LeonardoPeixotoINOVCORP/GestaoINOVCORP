import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import fs from 'fs';
import { resolve } from 'path';
import { defineConfig } from 'vite';

const host = 'gestao.test';
const certDir = 'C:\\Users\\yxng adel\\.config\\herd\\config\\valet\\Certificates';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host,
        https: {
            key:  fs.readFileSync(`${certDir}\\${host}.key`),
            cert: fs.readFileSync(`${certDir}\\${host}.crt`),
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});