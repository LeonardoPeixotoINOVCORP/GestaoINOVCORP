import { route as ziggyRoute } from 'ziggy-js';
import { Ziggy } from '@/ziggy';

export function useRoute() {
    return (name: string, params?: any, absolute?: boolean) => {
        return ziggyRoute(name, params, absolute, Ziggy as any).toString();
    };
}