import type { route as routeFn } from 'ziggy-js';

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof routeFn;
    }
}

declare global {
    var route: typeof routeFn;
}

export {};