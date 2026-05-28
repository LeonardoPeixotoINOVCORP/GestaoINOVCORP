import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const permissions = computed(() => {
        return (page.props.auth as any)?.permissions ?? [];
    });

    const roles = computed(() => {
        return (page.props.auth as any)?.roles ?? [];
    });

    function can(permission: string): boolean {
        if (roles.value.includes('Administrador')) {
            return true;
        }

        return permissions.value.includes(permission);
    }

    function isAdmin(): boolean {
        return roles.value.includes('Administrador');
    }

    return { can, isAdmin, permissions, roles };
}