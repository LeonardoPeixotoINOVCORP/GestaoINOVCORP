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
    conta?: { id: number; banco: string; iban: string; swift: string | null; titular: string; ativo: boolean };
}>()

const isEditing = !!props.conta;

const form = useForm({
    banco:   props.conta?.banco ?? '',
    iban:    props.conta?.iban ?? '',
    swift:   props.conta?.swift ?? '',
    titular: props.conta?.titular ?? '',
    ativo:   Boolean(props.conta?.ativo ?? true),
});

function submit() {
    if (isEditing) {
        form.put(route('contas-bancarias.update', props.conta!.id));
    } else {
        form.post(route('contas-bancarias.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Conta' : 'Nova Conta Bancária'" />
        <div class="p-6 max-w-lg mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ isEditing ? 'Editar Conta' : 'Nova Conta Bancária' }}</h1>
                <Link :href="route('contas-bancarias.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1">
                    <Label for="banco">Banco *</Label>
                    <Input id="banco" v-model="form.banco" />
                    <p v-if="form.errors.banco" class="text-sm text-destructive">{{ form.errors.banco }}</p>
                </div>
                <div class="space-y-1">
                    <Label for="iban">IBAN *</Label>
                    <Input id="iban" v-model="form.iban" class="font-mono" placeholder="PT50..." />
                    <p v-if="form.errors.iban" class="text-sm text-destructive">{{ form.errors.iban }}</p>
                </div>
                <div class="space-y-1">
                    <Label for="swift">SWIFT / BIC</Label>
                    <Input id="swift" v-model="form.swift" placeholder="BPIXXXXX" />
                </div>
                <div class="space-y-1">
                    <Label for="titular">Titular *</Label>
                    <Input id="titular" v-model="form.titular" />
                    <p v-if="form.errors.titular" class="text-sm text-destructive">{{ form.errors.titular }}</p>
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