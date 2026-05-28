<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import TenantSwitcher from '@/components/TenantSwitcher.vue'
import { usePermissions } from '@/composables/usePermissions';
import { useRoute } from '@/composables/useRoute';

const { props } = usePage();
const tenants = props.tenants as Array<{ id: number; nome: string; slug: string }>;
const currentTenant = props.tenant as { id: number; nome: string } | null;

const { can } = usePermissions();
const route = useRoute();
const page = usePage();

const temSubscricao = (page.props.auth as any)?.temSubscricao;

const dropdownAberto = ref<string | null>(null);

function toggleDropdown(nome: string) {
    dropdownAberto.value = dropdownAberto.value === nome ? null : nome;
}

function fecharDropdowns() {
    dropdownAberto.value = null;
}

function isActive(path: string): boolean {
    return page.url === path || page.url.startsWith(path + '/');
}

const menus = [
    {
        label: 'Dashboard',
        path: '/dashboard',
        routeName: 'dashboard',
        // sem permission = sempre visível
    },
    {
        label: 'Clientes',
        path: '/clientes',
        routeName: 'clientes.index',
        permission: 'read_clientes',
    },
    {
        label: 'Fornecedores',
        path: '/fornecedores',
        routeName: 'fornecedores.index',
        permission: 'read_fornecedores',
    },
    {
        label: 'Contactos',
        path: '/contactos',
        routeName: 'contactos.index',
        permission: 'read_contactos',
    },
    {
        label: 'Comercial',
        dropdown: [
            { label: 'Propostas', routeName: 'propostas.index', path: '/propostas', permission: 'read_propostas' },
            { label: 'Encomendas Clientes', routeName: 'encomendas.index', path: '/encomendas', permission: 'read_encomendas' },
            { label: 'Encomendas Fornecedor', routeName: 'encomendas.fornecedor.index', path: '/encomendas-fornecedor', permission: 'read_encomendas-fornecedor' },
        ],
    },
    {
        label: 'Financeiro',
        dropdown: [
            { label: 'Faturas Fornecedor', routeName: 'faturas-fornecedor.index', path: '/faturas-fornecedor', permission: 'read_faturas-fornecedor' },
            { label: 'Contas Bancárias', routeName: 'contas-bancarias.index', path: '/contas-bancarias', permission: 'read_contas-bancarias' },
            { label: 'Conta Corrente', routeName: 'conta-corrente.index', path: '/conta-corrente', permission: 'read_conta-corrente' },
        ],
    },
    {
        label: 'Calendário',
        path: '/calendario',
        routeName: 'calendario.index',
        permission: 'read_calendario',
    },
    {
        label: 'Arquivo',
        path: '/arquivo',
        routeName: 'arquivo.index',
        permission: 'read_arquivo',
    },
    {
        label: 'Configurações',
        dropdown: [
            { label: 'Empresa', routeName: 'empresa.edit', path: '/configuracoes/empresa', },
            { label: 'Países', routeName: 'configuracoes.paises.index', path: '/configuracoes/paises', permission: 'read_configuracoes' },
            { label: 'Funções de Contacto', routeName: 'configuracoes.contactos-funcoes.index', path: '/configuracoes/contactos-funcoes', permission: 'read_configuracoes' },
            { label: 'Artigos', routeName: 'configuracoes.artigos.index', path: '/configuracoes/artigos', permission: 'read_configuracoes' },
            { label: 'Tipos de Calendário', routeName: 'configuracoes.calendario-tipos.index', path: '/configuracoes/calendario-tipos', permission: 'read_configuracoes' },
            { label: 'Ações de Calendário', routeName: 'configuracoes.calendario-acoes.index', path: '/configuracoes/calendario-acoes', permission: 'read_configuracoes' },
        ],
    },
    {
        label: 'Gestão',
        dropdown: [
            { label: 'Utilizadores', routeName: 'gestao-acessos.utilizadores.index', path: '/gestao-acessos/utilizadores', permission: 'read_utilizadores' },
            { label: 'Permissões', routeName: 'gestao-acessos.permissoes.index', path: '/gestao-acessos/permissoes', permission: 'read_permissoes' },
            { label: 'Logs', routeName: 'logs.index', path: '/logs', permission: 'read_permissoes' },
            {
                label: 'Subscrição',
                routeName: temSubscricao
                    ? 'billing.index'
                    : 'billing.planos',
                path: temSubscricao
                    ? '/billing'
                    : '/billing/planos',
            },
        ],
    },
 
];

// Menus filtrados pelas permissões do utilizador atual
const menusVisiveis = computed(() => {
    return menus
        .map(menu => {
            if (menu.dropdown) {
                const itemsVisiveis = menu.dropdown.filter(
                    i => !i.permission || can(i.permission)
                );
                
                // Esconde o dropdown inteiro se não tiver nenhum item visível
                if (itemsVisiveis.length === 0) { 
                    return null; 
                }

                return { ...menu, dropdown: itemsVisiveis };
            }

            // Link simples: esconde se não tiver permissão
            if (menu.permission && !can(menu.permission)) {
            
            return null;
            }
            
            return menu;
        })
        .filter(Boolean) as typeof menus;
});

function isDropdownActive(items: Array<{ path: string }>): boolean {
    return items.some(i => isActive(i.path));
}

const limites = computed(() => (page.props.limites as string[]) ?? []);

const notificacoes = computed(() => (page.props.notificacoes as any[]) ?? []);
const mostrarNotificacoes = ref(false);

function marcarTodasLidas() {
    router.post(route('notificacoes.lerTodas'), {}, {
        preserveScroll: true,
        onSuccess: () => { 
            mostrarNotificacoes.value = false; 
        },
    });
}
</script>

<template>
    <header class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur">
        <div class="flex h-14 items-center gap-2 px-6">

            <!-- Logo -->
            <Link :href="route('dashboard')" class="font-semibold text-sm mr-4">
                Gestão
            </Link>

            <!-- Nav -->
            <nav class="flex items-center gap-1 flex-1">
                <template v-for="menu in menusVisiveis" :key="menu.label">

                    <!-- Link simples -->
                    <Link
                        v-if="!menu.dropdown"
                        :href="route(menu.routeName!)"
                        class="px-3 py-1.5 text-sm rounded-md transition-colors"
                        :class="isActive(menu.path!)
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
                    >
                        {{ menu.label }}
                    </Link>

                    <!-- Dropdown -->
                    <div v-else class="relative">
                        <button
                            class="flex items-center gap-1 px-3 py-1.5 text-sm rounded-md transition-colors cursor-pointer"
                            :class="isDropdownActive(menu.dropdown)
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
                            @click="toggleDropdown(menu.label)"
                        >
                            {{ menu.label }}

                            <svg
                                class="w-3 h-3 transition-transform"
                                :class="dropdownAberto === menu.label ? 'rotate-180' : ''"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>

                        <div
                            v-if="dropdownAberto === menu.label"
                            class="absolute top-full left-0 mt-1 w-52 rounded-md border bg-background shadow-lg z-50 py-1"
                        >
                            <!-- Itens já vêm filtrados por permissão via menusVisiveis -->
                            <Link
                                v-for="item in menu.dropdown"
                                :key="item.routeName"
                                :href="route(item.routeName)"
                                class="block px-4 py-2 text-sm transition-colors"
                                :class="isActive(item.path)
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:text-foreground hover:bg-muted'"
                                @click="fecharDropdowns"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                </template>
            </nav>

            <TenantSwitcher :tenants="tenants" :currentTenant="currentTenant" />

            <!-- Notificações -->
            <div class="relative" v-if="notificacoes.length > 0">
                <button
                    class="relative flex items-center justify-center w-8 h-8 rounded-md hover:bg-muted transition-colors"
                    @click="mostrarNotificacoes = !mostrarNotificacoes"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full" />
                </button>

                <div v-if="mostrarNotificacoes"
                    class="absolute right-0 top-full mt-1 w-80 rounded-md border bg-background shadow-lg z-50 py-1">
                    <div class="flex items-center justify-between px-4 py-2 border-b">
                        <span class="text-sm font-medium">Notificações</span>
                        <button
                            class="text-xs text-muted-foreground hover:text-foreground"
                            @click="marcarTodasLidas"
                        >
                            Marcar todas como lidas
                        </button>
                    </div>
                    <div v-for="n in notificacoes" :key="n.id"
                        class="px-4 py-3 text-sm border-b last:border-0 hover:bg-muted/50">
                        <p>{{ n.mensagem }}</p>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ new Date(n.created_at).toLocaleDateString('pt-PT') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Avisos de limite -->
             <div v-if="limites.length > 0" class="flex items-center gap-2">
                <Link :href="route('billing.index')" 
                    class="flex items-center gap-1.5 text-xs bg-amber-500/10 text-amber-600 border border-amber-500/20 rounded-md px-2 py-1 hover:bg-amber-500/20 transition-colors">
                    <span>⚠️</span>
                    <span>{{ limites[0] }}</span>
                    <span v-if="limites.length > 1" class="text-amber-500">+{{ limites.length - 1 }}</span>
                </Link>
            </div>

            <!-- Utilizador -->
            <div class="flex items-center gap-3">
                <span class="text-sm text-muted-foreground">
                    {{ (page.props.auth as any)?.user?.name }}
                </span>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                >
                    Sair
                </Link>
            </div>
        </div>
    
    </header>

    <!-- Overlay para fechar dropdowns -->
    <div
        v-if="dropdownAberto"
        class="fixed inset-0 z-30"
        @click="fecharDropdowns"
    />
</template>