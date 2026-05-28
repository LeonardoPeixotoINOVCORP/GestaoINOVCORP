<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();
const { can } = usePermissions();

defineProps<{
    stats: {
        clientes: number;
        fornecedores: number;
        propostas_abertas: number;
        encomendas_abertas: number;
        faturas_pendentes: number;
        faturas_valor_pendente: number;
    };
    propostas_recentes: Array<{
        id: number;
        numero: number;
        estado: string;
        entidade: { nome: string } | null;
        created_at: string;
    }>;
    encomendas_recentes: Array<{
        id: number;
        numero: number;
        estado: string;
        tipo: string;
        entidade: { nome: string } | null;
        created_at: string;
    }>;
    faturas_a_vencer: Array<{
        id: number;
        numero: number;
        valor_total: number;
        data_vencimento: string;
        fornecedor: { nome: string } | null;
    }>;
    atividade_recente: Array<{
        id: number;
        description: string;
        created_at: string;
        causer: { name: string } | null;
        subject_type: string | null;
    }>;
}>();

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('pt-PT');
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(value);
}

function formatSubject(type: string | null): string {
    if (!type) {

        return '';
    }
    
    return type.split('\\').pop() ?? '';
}

function estadoBadgeClass(estado: string): string {
    return estado === 'fechado'
        ? 'bg-green-500/10 text-green-500 border border-green-500/20'
        : 'bg-amber-500/10 text-amber-500 border border-amber-500/20';
}

function diasAteVencer(date: string): number {
    const diff = new Date(date).getTime() - new Date().getTime();
    
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
}
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="p-6 space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
                <p class="text-sm text-muted-foreground mt-1">Visão geral do negócio</p>
            </div>

            <!-- Stats Grid — só mostra cards com permissão -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <Link v-if="can('read_clientes')" :href="route('clientes.index')"
                    class="rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors space-y-1">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Clientes</p>
                    <p class="text-3xl font-bold tabular-nums">{{ stats.clientes }}</p>
                </Link>

                <Link v-if="can('read_fornecedores')" :href="route('fornecedores.index')"
                    class="rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors space-y-1">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Fornecedores</p>
                    <p class="text-3xl font-bold tabular-nums">{{ stats.fornecedores }}</p>
                </Link>

                <Link v-if="can('read_propostas')" :href="route('propostas.index')"
                    class="rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors space-y-1">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Propostas Abertas</p>
                    <p class="text-3xl font-bold tabular-nums">{{ stats.propostas_abertas }}</p>
                </Link>

                <Link v-if="can('read_encomendas')" :href="route('encomendas.index')"
                    class="rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors space-y-1">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Encomendas Abertas</p>
                    <p class="text-3xl font-bold tabular-nums">{{ stats.encomendas_abertas }}</p>
                </Link>

                <Link v-if="can('read_faturas-fornecedor')" :href="route('faturas-fornecedor.index')"
                    class="rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors space-y-1">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Faturas Pendentes</p>
                    <p class="text-3xl font-bold tabular-nums">{{ stats.faturas_pendentes }}</p>
                </Link>

                <Link v-if="can('read_faturas-fornecedor')" :href="route('faturas-fornecedor.index')"
                    class="rounded-lg border bg-card p-4 hover:bg-muted/50 transition-colors space-y-1">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Valor em Dívida</p>
                    <p class="text-2xl font-bold tabular-nums">{{ formatCurrency(stats.faturas_valor_pendente) }}</p>
                </Link>
            </div>

            <!-- Middle Row — adapta colunas ao número de secções visíveis -->
            <div
                v-if="can('read_propostas') || can('read_encomendas') || can('read_faturas-fornecedor')"
                class="grid grid-cols-1 gap-4"
                :class="{
                    'lg:grid-cols-3': can('read_propostas') && can('read_encomendas') && can('read_faturas-fornecedor'),
                    'lg:grid-cols-2': [can('read_propostas'), can('read_encomendas'), can('read_faturas-fornecedor')].filter(Boolean).length === 2,
                }"
            >
                <!-- Propostas Recentes -->
                <div v-if="can('read_propostas')" class="rounded-lg border bg-card">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <h2 class="text-sm font-medium">Propostas Recentes</h2>
                        <Link :href="route('propostas.index')" class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                            Ver todas →
                        </Link>
                    </div>
                    <div class="divide-y">
                        <div v-for="p in propostas_recentes" :key="p.id"
                            class="flex items-center justify-between px-4 py-3 hover:bg-muted/30 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ p.entidade?.nome ?? '—' }}</p>
                                <p class="text-xs text-muted-foreground">Nº {{ p.numero }} · {{ formatDate(p.created_at) }}</p>
                            </div>
                            <span class="ml-3 shrink-0 text-xs px-2 py-0.5 rounded-full capitalize" :class="estadoBadgeClass(p.estado)">
                                {{ p.estado }}
                            </span>
                        </div>
                        <div v-if="propostas_recentes.length === 0" class="px-4 py-6 text-center text-sm text-muted-foreground">
                            Sem propostas
                        </div>
                    </div>
                </div>

                <!-- Encomendas Recentes -->
                <div v-if="can('read_encomendas')" class="rounded-lg border bg-card">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <h2 class="text-sm font-medium">Encomendas Recentes</h2>
                        <Link :href="route('encomendas.index')" class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                            Ver todas →
                        </Link>
                    </div>
                    <div class="divide-y">
                        <div v-for="e in encomendas_recentes" :key="e.id"
                            class="flex items-center justify-between px-4 py-3 hover:bg-muted/30 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ e.entidade?.nome ?? '—' }}</p>
                                <p class="text-xs text-muted-foreground">Nº {{ e.numero }} · {{ e.tipo }} · {{ formatDate(e.created_at) }}</p>
                            </div>
                            <span class="ml-3 shrink-0 text-xs px-2 py-0.5 rounded-full capitalize" :class="estadoBadgeClass(e.estado)">
                                {{ e.estado }}
                            </span>
                        </div>
                        <div v-if="encomendas_recentes.length === 0" class="px-4 py-6 text-center text-sm text-muted-foreground">
                            Sem encomendas
                        </div>
                    </div>
                </div>

                <!-- Faturas a Vencer -->
                <div v-if="can('read_faturas-fornecedor')" class="rounded-lg border bg-card">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <h2 class="text-sm font-medium">Faturas a Vencer</h2>
                        <Link :href="route('faturas-fornecedor.index')" class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                            Ver todas →
                        </Link>
                    </div>
                    <div class="divide-y">
                        <div v-for="f in faturas_a_vencer" :key="f.id"
                            class="flex items-center justify-between px-4 py-3 hover:bg-muted/30 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ f.fornecedor?.nome ?? '—' }}</p>
                                <p class="text-xs text-muted-foreground">Nº {{ f.numero }} · {{ formatDate(f.data_vencimento) }}</p>
                            </div>
                            <div class="ml-3 shrink-0 text-right">
                                <p class="text-sm font-semibold tabular-nums">{{ formatCurrency(f.valor_total) }}</p>
                                <p class="text-xs" :class="diasAteVencer(f.data_vencimento) <= 7 ? 'text-red-500' : 'text-muted-foreground'">
                                    {{ diasAteVencer(f.data_vencimento) }}d
                                </p>
                            </div>
                        </div>
                        <div v-if="faturas_a_vencer.length === 0" class="px-4 py-6 text-center text-sm text-muted-foreground">
                            Sem faturas a vencer
                        </div>
                    </div>
                </div>
            </div>

            <!-- Atividade Recente — só para quem tem read_permissoes (Gestão/Admin) -->
            <div v-if="can('read_permissoes')" class="rounded-lg border bg-card">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h2 class="text-sm font-medium">Atividade Recente</h2>
                    <Link :href="route('logs.index')" class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                        Ver logs →
                    </Link>
                </div>
                <div class="divide-y">
                    <div v-for="log in atividade_recente" :key="log.id"
                        class="flex items-center gap-3 px-4 py-3">
                        <div class="w-7 h-7 rounded-full bg-muted flex items-center justify-center shrink-0 text-xs font-medium">
                            {{ log.causer?.name?.charAt(0).toUpperCase() ?? '?' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm">
                                <span class="font-medium">{{ log.causer?.name ?? 'Sistema' }}</span>
                                <span class="text-muted-foreground">&nbsp; {{ log.description }}</span>
                                <span v-if="log.subject_type" class="text-muted-foreground"> ({{ formatSubject(log.subject_type) }})</span>
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground shrink-0">{{ formatDate(log.created_at) }}</p>
                    </div>
                    <div v-if="atividade_recente.length === 0" class="px-4 py-6 text-center text-sm text-muted-foreground">
                        Sem atividade registada
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>