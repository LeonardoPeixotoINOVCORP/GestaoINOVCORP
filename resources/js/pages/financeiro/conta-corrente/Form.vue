<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    clientes: Array<{ id: number; nome: string }>;
    movimento?: {
        id: number;
        entidade_id: number;
        data: string;
        descricao: string;
        valor: number;
        tipo: string;
        referencia: string | null;
    };
}>()

const isEditing = !!props.movimento;

const form = useForm({
    entidade_id: props.movimento?.entidade_id?.toString() ?? '',
    data_movimento:        props.movimento?.data ?? '',
    descricao:   props.movimento?.descricao ?? '',
    valor:       props.movimento?.valor ?? 0,
    tipo:        props.movimento?.tipo ?? 'credito',
    referencia:  props.movimento?.referencia ?? '',
});

function submit() {
    if (isEditing) {
        form.put(route('conta-corrente.update', props.movimento!.id));
    } else {
        form.post(route('conta-corrente.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Movimento' : 'Novo Movimento'" />
        <div class="p-6 max-w-lg mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ isEditing ? 'Editar Movimento' : 'Novo Movimento' }}</h1>
                <Link :href="route('conta-corrente.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1">
                    <Label>Cliente *</Label>
                    <Select v-model="form.entidade_id">
                        <SelectTrigger><SelectValue placeholder="Selecionar cliente" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="c in clientes" :key="c.id" :value="c.id.toString()">
                                {{ c.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.entidade_id" class="text-sm text-destructive">{{ form.errors.entidade_id }}</p>
                </div>
                <div class="space-y-1">
                    <Label for="data_movimento">Data de Movimento *</Label>
                    <Input id="data_movimento" v-model="form.data_movimento" type="date" />
                    <p v-if="form.errors.data_movimento" class="text-sm text-destructive">{{ form.errors.data_movimento }}</p>
                </div>
                <div class="space-y-1">
                    <Label for="descricao">Descrição *</Label>
                    <Input id="descricao" v-model="form.descricao" />
                    <p v-if="form.errors.descricao" class="text-sm text-destructive">{{ form.errors.descricao }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="valor">Valor *</Label>
                        <Input id="valor" v-model.number="form.valor" type="number" step="0.01" min="0" />
                        <p v-if="form.errors.valor" class="text-sm text-destructive">{{ form.errors.valor }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Tipo *</Label>
                        <Select v-model="form.tipo">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="credito">Crédito</SelectItem>
                                <SelectItem value="debito">Débito</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <div class="space-y-1">
                    <Label for="referencia">Referência</Label>
                    <Input id="referencia" v-model="form.referencia" />
                </div>
                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>