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
    tipo?: { id: number; nome: string; cor: string; ativo: boolean };
}>()

const isEditing = !!props.tipo;

const form = useForm({
    nome:  props.tipo?.nome ?? '',
    cor:   props.tipo?.cor ?? '#3b82f6',
    ativo: Boolean(props.tipo?.ativo ?? true),
});

function submit() {
    if (isEditing) {
        form.put(route('configuracoes.calendario-tipos.update', props.tipo!.id));
    } else {
        form.post(route('configuracoes.calendario-tipos.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Tipo' : 'Novo Tipo'" />
        <div class="p-6 max-w-lg mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ isEditing ? 'Editar Tipo' : 'Novo Tipo' }}</h1>
                <Link :href="route('configuracoes.calendario-tipos.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1">
                    <Label for="nome">Nome *</Label>
                    <Input id="nome" v-model="form.nome" />
                    <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                </div>
                <div class="space-y-1">
                    <Label for="cor">Cor</Label>
                    <div class="flex items-center gap-3">
                        <input id="cor" v-model="form.cor" type="color" class="h-10 w-16 rounded border cursor-pointer" />
                        <span class="text-sm text-muted-foreground">{{ form.cor }}</span>
                    </div>
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