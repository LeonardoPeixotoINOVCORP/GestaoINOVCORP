<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    subscription: {
        name: string;
        stripe_status: string;
        ends_at: string | null;
        trial_ends_at: string | null;
        is_trial: boolean;
        trial_days_left: number;
    } | null;
    utilizacao: {
        utilizadores: { atual: number; max: number };
        clientes:     { atual: number; max: number };
        artigos:      { atual: number; max: number };
        arquivo_digital: boolean;
        calendario:      boolean;
        financeiro:      boolean;
    };
    plano: {
        id: number;
        nome: string;
        preco: number;
        slug: string;
    } | null;
}>()

function percentagem(atual: number, max: number): number {
    if (max >= 999999) { 
        return 5; 
    }

    if (max === 0) { 
        return 0; 
    }

    return Math.min(100, Math.round((atual / max) * 100));
}

function corBarra(pct: number): string {
    if (pct >= 90) {
         return 'bg-red-500'; 
        }

    if (pct >= 70) {
         return 'bg-amber-500';
     }

    return 'bg-primary';
}

function formatMax(max: number): string {
    if (max >= 999999) {
         return '∞'; 
        }

    return max.toString();
}

function formatDate(date: string | null): string {
    if (!date) { 
        return '—'; 
    }

    return new Date(date).toLocaleDateString('pt-PT');
}

const statusBadge = computed(() => {
    if (!props.subscription) { 
        return { label: 'Sem plano', variant: 'secondary' as const }; 
    }

    if (props.subscription.is_trial) { 
        return { label: `Trial — ${props.subscription.trial_days_left} dias`, variant: 'secondary' as const }; 
    }

    if (props.subscription.stripe_status === 'active') { 
        return { label: 'Ativo', variant: 'default' as const }; 
    }

    if (props.subscription.stripe_status === 'canceled') {
         return { label: 'Cancelado', variant: 'destructive' as const }; 
        }

    if (props.subscription.stripe_status === 'past_due') {
         return { label: 'Pagamento em falta', variant: 'destructive' as const };
        }

    return { label: props.subscription.stripe_status, variant: 'secondary' as const };
});
</script>

<template>
    <AppLayout>
        <Head title="Subscrição" />
        <div class="p-6 max-w-4xl mx-auto space-y-6">

            <!-- Flash messages -->
            <div v-if="($page.props.flash as any)?.sucesso"
                class="rounded-lg bg-green-100 text-green-700 p-3 text-sm">
                {{ ($page.props.flash as any).sucesso }}
            </div>
            <div v-if="($page.props.flash as any)?.erro"
                class="rounded-lg bg-red-100 text-red-700 p-3 text-sm">
                {{ ($page.props.flash as any).erro }}
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Subscrição</h1>
                    <p class="text-sm text-muted-foreground">Plano atual e utilização</p>
                </div>
                <Link :href="route('billing.planos')">
                    <Button variant="outline" class="cursor-pointer">
                        {{ plano ? 'Mudar plano' : 'Escolher plano' }}
                    </Button>
                </Link>
            </div>

            <!-- Plano atual -->
            <div class="rounded-lg border p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Plano atual</p>
                        <p class="text-xl font-bold">{{ subscription?.name ?? 'Sem plano' }}</p>
                    </div>
                    <Badge :variant="statusBadge.variant">{{ statusBadge.label }}</Badge>
                </div>

                <!-- Trial warning -->
                <div v-if="subscription?.is_trial && subscription.trial_days_left <= 14"
                    class="rounded-md bg-amber-300/10 border border-amber-500/20 p-3 text-sm text-amber-600">
                    O teu trial termina em <strong>{{ subscription.trial_days_left }} dias</strong>
                    ({{ formatDate(subscription.trial_ends_at) }}).
                    <Link :href="route('billing.planos')" class="underline ml-1">Subscreve agora</Link>
                    para não perder o acesso.
                </div>

                <div v-if="subscription?.ends_at" class="text-sm text-muted-foreground">
                    Acesso até: {{ formatDate(subscription.ends_at) }}
                </div>

                
                <div v-if="plano" class="flex items-center gap-4 pt-2 border-t">

                   
                        <a href="/billing/portal">
                            <Button variant="outline" size="sm" class="cursor-pointer">
                                Gerir faturação
                            </Button>
                            
                        </a>
                        
                        <form method="POST" :action="route('billing.cancelar')"
                            @submit.prevent="$inertia.post(route('billing.cancelar'))">
                            <Button
                                v-if="subscription?.stripe_status === 'active'"
                                type="submit"
                                variant="ghost"
                                size="sm"
                                class="cursor-pointer text-destructive hover:text-destructive"
                            >
                                Cancelar subscrição
                            </Button>
                        </form>
                

                    <Link :href="route('billing.logs')">
                            <Button variant="ghost" size="sm" class="cursor-pointer">
                                Ver histórico
                            </Button>
                    </Link>
                    
                </div>

            </div>

            <!-- Utilização -->
            <div class="rounded-lg border p-6 space-y-5">
                <h2 class="font-medium">Utilização do plano</h2>

                <!-- Utilizadores -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Utilizadores</span>
                        <span class="text-muted-foreground">
                            {{ utilizacao.utilizadores.atual }} / {{ formatMax(utilizacao.utilizadores.max) }}
                        </span>
                    </div>
                    <div class="h-2 bg-muted rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                            :class="corBarra(percentagem(utilizacao.utilizadores.atual, utilizacao.utilizadores.max))"
                            :style="{ width: `${percentagem(utilizacao.utilizadores.atual, utilizacao.utilizadores.max)}%` }" />
                    </div>
                </div>

                <!-- Clientes -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Clientes</span>
                        <span class="text-muted-foreground">
                            {{ utilizacao.clientes.atual }} / {{ formatMax(utilizacao.clientes.max) }}
                        </span>
                    </div>
                    <div class="h-2 bg-muted rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                            :class="corBarra(percentagem(utilizacao.clientes.atual, utilizacao.clientes.max))"
                            :style="{ width: `${percentagem(utilizacao.clientes.atual, utilizacao.clientes.max)}%` }" />
                    </div>
                </div>

                <!-- Artigos -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Artigos</span>
                        <span class="text-muted-foreground">
                            {{ utilizacao.artigos.atual }} / {{ formatMax(utilizacao.artigos.max) }}
                        </span>
                    </div>
                    <div class="h-2 bg-muted rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                            :class="corBarra(percentagem(utilizacao.artigos.atual, utilizacao.artigos.max))"
                            :style="{ width: `${percentagem(utilizacao.artigos.atual, utilizacao.artigos.max)}%` }" />
                    </div>
                </div>

                <!-- Funcionalidades -->
                <div class="pt-2 border-t space-y-2">
                    <p class="text-sm font-medium">Funcionalidades incluídas</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="flex items-center gap-2 text-sm"
                            :class="utilizacao.arquivo_digital ? 'text-foreground' : 'text-muted-foreground line-through'">
                            <span>{{ utilizacao.arquivo_digital ? '✓' : '✗' }}</span>
                            Arquivo Digital
                        </div>
                        <div class="flex items-center gap-2 text-sm"
                            :class="utilizacao.calendario ? 'text-foreground' : 'text-muted-foreground line-through'">
                            <span>{{ utilizacao.calendario ? '✓' : '✗' }}</span>
                            Calendário
                        </div>
                        <div class="flex items-center gap-2 text-sm"
                            :class="utilizacao.financeiro ? 'text-foreground' : 'text-muted-foreground line-through'">
                            <span>{{ utilizacao.financeiro ? '✓' : '✗' }}</span>
                            Financeiro
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>