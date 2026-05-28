<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

defineProps<{
    planos: Array<{
        id: number;
        nome: string;
        slug: string;
        preco: number;
        max_utilizadores: number;
        max_clientes: number;
        max_artigos: number;
        arquivo_digital: boolean;
        calendario: boolean;
        financeiro: boolean;
        trial_dias: number;
        stripe_price_id: string | null;
    }>;
    tenant: any;
}>()

const stripe = ref<any>(null);
const card = ref<any>(null);
const planoSelecionado = ref<any>(null);
const processando = ref(false);
const erro = ref('');

onMounted(async () => {
    stripe.value = await loadStripe(import.meta.env.VITE_STRIPE_KEY);
});

function selecionarPlano(plano: any) {
    if (plano.slug === 'free') {
        subscreverGratuito(plano.id);
        
        return;
    }

    planoSelecionado.value = plano;

    setTimeout(() => {
        const elements = stripe.value.elements();
        card.value = elements.create('card', {
            hidePostalCode: true,  
            style: {
                base: {
                    fontSize: '16px',
                    color: '#ffffff',         
                    fontFamily: 'inherit',
                    '::placeholder': {
                        color: '#71717a',      
                    },
                    iconColor: '#ffffff',        
                },
                invalid: {
                    color: '#ef4444',          
                    iconColor: '#ef4444',
                },
            },
        });
        card.value.mount('#card-element');
    }, 100);
}

function subscreverGratuito(planoId: number) {
    router.post(route('billing.subscrever', planoId), {});
}

async function confirmarPagamento() {
    if (!stripe.value || !card.value) { 
        return;
     }

    processando.value = true;
    erro.value = '';

    const { paymentMethod, error } = await stripe.value.createPaymentMethod({
        type: 'card',
        card: card.value,
    });

    if (error) {
        erro.value = error.message ?? 'Erro ao processar cartão.';
        processando.value = false;

        return;
    }

    router.post(route('billing.subscrever', planoSelecionado.value.id), {
        payment_method: paymentMethod.id,
    }, {
        onFinish: () => {
            processando.value = false;
        },
    });
}

</script>

<template>
    <AppLayout>
        <Head title="Planos" />
        <div class="p-6 max-w-5xl mx-auto">
            <h1 class="text-3xl font-bold mb-2">Planos</h1>
            <p class="text-muted-foreground mb-8">
                Escolha o plano mais adequado para a sua empresa.
            </p>

            <!-- Grid de planos -->
            <div v-if="!planoSelecionado" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="plano in planos"
                    :key="plano.id"
                    class="border rounded-2xl p-6 flex flex-col"
                    :class="plano.slug === 'pro' ? 'border-primary shadow-md' : ''"
                >
                    <div v-if="plano.slug === 'pro'" class="text-xs font-bold text-primary mb-2 uppercase">
                        Mais popular
                    </div>
                    <h2 class="text-2xl font-bold mb-2">{{ plano.nome }}</h2>
                    <div class="mb-4">
                        <span class="text-4xl font-bold">{{ Number(plano.preco).toFixed(2) }} €</span>
                        <span class="text-muted-foreground"> / mês</span>
                    </div>
                    <div v-if="plano.trial_dias > 0 && plano.preco > 0" class="text-sm text-green-600 mb-4">
                        {{ plano.trial_dias }} dias grátis
                    </div>
                    <div class="space-y-2 mb-6 flex-1 text-sm">
                        <div>✓ {{ plano.max_utilizadores === 999 ? 'Utilizadores ilimitados' : `${plano.max_utilizadores} utilizadores` }}</div>
                        <div>✓ {{ plano.max_clientes === 999999 ? 'Clientes ilimitados' : `${plano.max_clientes} clientes` }}</div>
                        <div>✓ {{ plano.max_artigos === 999999 ? 'Artigos ilimitados' : `${plano.max_artigos} artigos` }}</div>
                        <div v-if="plano.arquivo_digital">✓ Arquivo Digital</div>
                        <div v-if="plano.financeiro">✓ Módulo Financeiro</div>
                        <div v-if="plano.calendario">✓ Calendário</div>
                    </div>
                    <Button
                        class="cursor-pointer w-full"
                        :variant="plano.slug === 'pro' ? 'default' : 'outline'"
                        @click="selecionarPlano(plano)"
                    >
                        {{ plano.preco === 0 ? 'Usar gratuitamente' : 'Subscrever' }}
                    </Button>
                </div>
            </div>

            <!-- Formulário de pagamento -->
            <div v-else class="max-w-md mx-auto space-y-6">
                <div class="rounded-lg border p-6">
                    <h2 class="font-semibold text-lg mb-1">{{ planoSelecionado.nome }}</h2>
                    <p class="text-muted-foreground text-sm mb-4">
                        {{ Number(planoSelecionado.preco).toFixed(2) }} € / mês
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Dados do cartão</label>
                            <div id="card-element" class="border rounded-md p-3 bg-background" />
                            <p v-if="erro" class="text-sm text-destructive mt-2">{{ erro }}</p>
                        </div>

                        <Button
                            class="cursor-pointer w-full"
                            :disabled="processando"
                            @click="confirmarPagamento"
                        >
                            {{ processando ? 'A processar...' : `Subscrever — ${Number(planoSelecionado.preco).toFixed(2)} €/mês` }}
                        </Button>

                        <button
                            type="button"
                            class="w-full text-sm text-muted-foreground hover:text-foreground"
                            @click="planoSelecionado = null"
                        >
                            ← Voltar aos planos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>