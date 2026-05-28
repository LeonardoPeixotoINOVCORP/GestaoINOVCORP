import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, createSSRApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

import { initializeTheme } from '@/composables/useAppearance';
import { initializeFlashToast } from '@/lib/flashToast';
import { Ziggy } from './ziggy.js';

const appName = import.meta.env.VITE_APP_NAME || 'Gestao';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: async (name) => {
        const pages = import.meta.glob('./pages/**/*.vue');
        const page: any = await pages[`./pages/${name}.vue`]();
        page.default.layout = page.default.layout ?? null;
        
        return page;
    },

    setup({ el, App, props, plugin }) {
        const isServer = import.meta.env.SSR;
        const vueApp = isServer ? createSSRApp : createApp;

        const app = vueApp({
            render: () => h(App, props),
        });

        app.use(plugin)
            .use(ZiggyVue, Ziggy as any);

        if (!isServer) {
            app.mount(el);
        }

        return app;
    },

    progress: {
        color: '#4B5563',
    },
});

if (typeof window !== 'undefined') {
    initializeTheme();
    initializeFlashToast();
}