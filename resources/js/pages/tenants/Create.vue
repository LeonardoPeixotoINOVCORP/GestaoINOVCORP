<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    plans: Array<{
        id: number;
        nome: string;
        preco: number;
        max_utilizadores: number;
        trial_dias: number;
    }>;
}>();

const form = useForm({
    name: '',
    plan_id: props.plans[0]?.id || null,
});

function submit() {
    form.post(route('tenant.store'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Criar nova empresa" />

        <div class="max-w-2xl mx-auto py-8 px-4">
            <h1 class="text-2xl font-bold mb-6">
                Criar nova empresa (tenant)
            </h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Nome da empresa *
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full border rounded-md px-3 py-2"
                        placeholder="Ex: Minha Empresa Ltda"
                    />

                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Plano inicial
                    </label>

                    <select
                        v-model="form.plan_id"
                        class="w-full border rounded-md px-3 py-2"
                    >
                        <option
                            v-for="plan in plans"
                            :key="plan.id"
                            :value="plan.id"
                        >
                            {{ plan.nome }} - {{ plan.preco }}€/mês
                            ({{ plan.trial_dias }} dias grátis)
                        </option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('dashboard')" class="px-4 py-2 border rounded-md">
                        Cancelar
                    </Link>

                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'A criar...' : 'Criar empresa' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>