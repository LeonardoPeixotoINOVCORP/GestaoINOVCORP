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
    fornecedores: Array<{ id: number; nome: string }>;
    encomendas: Array<{ id: number; numero: number; entidade_id: number }>;
    fatura?: {
        id: number;
        data_fatura: string;
        data_vencimento: string | null;
        fornecedor_id: number;
        encomenda_id: number | null;
        valor_total: number;
        estado: string;
    };
}>()

const isEditing = !!props.fatura;

const form = useForm({
    data_fatura:     props.fatura?.data_fatura ?? '',
    data_vencimento: props.fatura?.data_vencimento ?? '',
    fornecedor_id:   props.fatura?.fornecedor_id?.toString() ?? '',
    encomenda_id:    props.fatura?.encomenda_id?.toString() ?? '',
    valor_total:     props.fatura?.valor_total ?? 0,
    documento:       null as File | null,
    estado:          props.fatura?.estado ?? 'pendente',
});

function submit() {
    if (isEditing) {
        form.put(route('faturas-fornecedor.update', props.fatura!.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('faturas-fornecedor.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Fatura' : 'Nova Fatura'" />
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ isEditing ? 'Editar Fatura' : 'Nova Fatura' }}</h1>
                <Link :href="route('faturas-fornecedor.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="data_fatura">Data da Fatura *</Label>
                        <Input id="data_fatura" v-model="form.data_fatura" type="date" />
                        <p v-if="form.errors.data_fatura" class="text-sm text-destructive">{{ form.errors.data_fatura }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="data_vencimento">Data de Vencimento</Label>
                        <Input id="data_vencimento" v-model="form.data_vencimento" type="date" />
                    </div>
                </div>

                <div class="space-y-1">
                    <Label>Fornecedor *</Label>
                    <Select v-model="form.fornecedor_id">
                        <SelectTrigger><SelectValue placeholder="Selecionar fornecedor" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="f in fornecedores" :key="f.id" :value="f.id.toString()">
                                {{ f.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.fornecedor_id" class="text-sm text-destructive">{{ form.errors.fornecedor_id }}</p>
                </div>

                <div class="space-y-1">
                    <Label>Encomenda Fornecedor</Label>
                    <Select v-model="form.encomenda_id">
                        <SelectTrigger><SelectValue placeholder="Selecionar encomenda (opcional)" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">— Nenhuma —</SelectItem>
                            <SelectItem v-for="e in encomendas" :key="e.id" :value="e.id.toString()">
                                Encomenda #{{ e.numero }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-1">
                    <Label for="valor_total">Valor Total *</Label>
                    <Input id="valor_total" v-model.number="form.valor_total" type="number" step="0.01" min="0" />
                    <p v-if="form.errors.valor_total" class="text-sm text-destructive">{{ form.errors.valor_total }}</p>
                </div>

                <div class="space-y-1">
                    <Label for="documento">Documento (PDF/Imagem)</Label>
                    <Input id="documento" type="file"
                        @change="(e: Event) => form.documento = (e.target as HTMLInputElement).files?.[0] ?? null" />
                </div>

                <div class="space-y-1">
                    <Label>Estado</Label>
                    <Select v-model="form.estado">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pendente">Pendente de Pagamento</SelectItem>
                            <SelectItem value="paga">Paga</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>