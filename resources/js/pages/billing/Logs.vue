<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

defineProps<{
    logs: {
        data: Array<{
            id: number;
            acao: string;
            valor_pago: number | null;
            notas: string | null;
            created_at: string;
            user: { name: string } | null;
            plan_anterior: { nome: string } | null;
            plan_novo: { nome: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('pt-PT');
}

function formatValor(valor: number | null): string {
    if (!valor) { 
        return '—'; 
    }

    return Number(valor).toFixed(2) + ' €';
}

const acaoBadge: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' }> = {
    subscribe:    { label: 'Subscrição',  variant: 'default' },
    upgrade:      { label: 'Upgrade',     variant: 'default' },
    downgrade:    { label: 'Downgrade',   variant: 'secondary' },
    cancel:       { label: 'Cancelado',   variant: 'destructive' },
    trial_start:  { label: 'Trial',       variant: 'secondary' },
    trial_end:    { label: 'Trial expirado', variant: 'destructive' },
};

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Logs de Plano" />
        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Logs de Plano</h1>
                    <p class="text-sm text-muted-foreground">Histórico de alterações de subscrição</p>
                </div>
                <Link :href="route('billing.index')">
                    <button class="text-sm text-muted-foreground hover:text-foreground">← Voltar</button>
                </Link>
            </div>

            <div class="rounded-lg border overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-muted">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Data</th>
                            <th class="text-left px-4 py-3 font-medium">Utilizador</th>
                            <th class="text-left px-4 py-3 font-medium">Ação</th>
                            <th class="text-left px-4 py-3 font-medium">Plano Anterior</th>
                            <th class="text-left px-4 py-3 font-medium">Plano Novo</th>
                            <th class="text-left px-4 py-3 font-medium">Valor</th>
                            <th class="text-left px-4 py-3 font-medium">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-muted/30">
                            <td class="px-4 py-3">{{ formatDate(log.created_at) }}</td>
                            <td class="px-4 py-3">{{ log.user?.name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="acaoBadge[log.acao]?.variant ?? 'secondary'">
                                    {{ acaoBadge[log.acao]?.label ?? log.acao }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ log.plan_anterior?.nome ?? '—' }}</td>
                            <td class="px-4 py-3">{{ log.plan_novo?.nome ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">{{ formatValor(log.valor_pago) }}</td>
                            <td class="px-4 py-3 text-muted-foreground text-xs">{{ log.notas ?? '—' }}</td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum registo encontrado.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex gap-1">
                <template v-for="link in logs.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="px-3 py-1 text-sm rounded border"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'">
                        {{ decodeHtml(link.label) }}
                    </Link>
                    <span v-else class="px-3 py-1 text-sm rounded border text-muted-foreground cursor-not-allowed">
                        {{ decodeHtml(link.label) }}
                    </span>
                </template>
            </div>
        </div>
    </AppLayout>
</template>