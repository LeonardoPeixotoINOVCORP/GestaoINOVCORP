<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    acao?: { id: number; nome: string; ativo: boolean };
}>()

const isEditing = !!props.acao;

const form = useForm({
    nome:  props.acao?.nome ?? '',
    ativo: Boolean(props.acao?.ativo ?? true),
});

function submit() {
    if (isEditing) {
        form.put(route('configuracoes.calendario-acoes.update', props.acao!.id));
    } else {
        form.post(route('configuracoes.calendario-acoes.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Ação' : 'Nova Ação'" />
        <div class="p-6 max-w-lg mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ isEditing ? 'Editar Ação' : 'Nova Ação' }}</h1>
                <Link :href="route('configuracoes.calendario-acoes.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1">
                    <Label for="nome">Nome *</Label>
                    <Input id="nome" v-model="form.nome" />
                    <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox id="ativo" :model-value="form.ativo"
                        @update:modelValue="(val) => form.ativo = val === true" />
                    <Label for="ativo">Ativo</Label>
                </div>
                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>