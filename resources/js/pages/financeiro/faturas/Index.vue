<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table, TableBody, TableCell,
    TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

defineProps<{
    faturas: {
        data: Array<{
            id: number;
            numero: number;
            data_fatura: string;
            data_vencimento: string | null;
            valor_total: number;
            estado: 'pendente' | 'paga';
            documento: string | null;
            fornecedor: { id: number; nome: string; email: string | null } | null;
            encomenda: { id: number; numero: number } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

const mostrarModalComprovativo = ref(false);
const faturaAtual = ref<number | null>(null);
const ficheiroCoprovativo = ref<File | null>(null);

function formatDate(date: string | null): string {
    if (!date) { 
        return '—'; 
    }
    
    return new Date(date).toLocaleDateString('pt-PT');
}

function formatValor(valor: number): string {
    return Number(valor).toFixed(2) + ' €';
}

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover esta fatura?')) {
        router.delete(route('faturas-fornecedor.destroy', id));
    }
}

function abrirModalComprovativo(id: number) {
    faturaAtual.value = id;
    mostrarModalComprovativo.value = true;
}

function enviarComprovativo() {
    if (!faturaAtual.value || !ficheiroCoprovativo.value) { 
        return; 
    }

    const formData = new FormData();
    formData.append('comprovativo', ficheiroCoprovativo.value);

    router.post(route('faturas-fornecedor.comprovativo', faturaAtual.value), formData as any, {
        onSuccess: () => {
            mostrarModalComprovativo.value = false;
            faturaAtual.value = null;
            ficheiroCoprovativo.value = null;
        },
    });
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Faturas Fornecedor" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Faturas Fornecedor</h1>
                <Link :href="route('faturas-fornecedor.create')">
                    <Button class="cursor-pointer">Nova Fatura</Button>
                </Link>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nº</TableHead>
                            <TableHead>Data</TableHead>
                            <TableHead>Vencimento</TableHead>
                            <TableHead>Fornecedor</TableHead>
                            <TableHead>Encomenda</TableHead>
                            <TableHead>Valor Total</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="fatura in faturas.data" :key="fatura.id">
                            <TableCell class="font-mono">{{ fatura.numero }}</TableCell>
                            <TableCell>{{ formatDate(fatura.data_fatura) }}</TableCell>
                            <TableCell>{{ formatDate(fatura.data_vencimento) }}</TableCell>
                            <TableCell>{{ fatura.fornecedor?.nome ?? '—' }}</TableCell>
                            <TableCell>{{ fatura.encomenda ? `#${fatura.encomenda.numero}` : '—' }}</TableCell>
                            <TableCell>{{ formatValor(fatura.valor_total) }}</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="fatura.estado === 'paga' ? 'default' : 'secondary'">
                                    {{ fatura.estado === 'paga' ? 'Paga' : 'Pendente' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <a v-if="fatura.documento" :href="route('faturas-fornecedor.download', fatura.id)" target="_blank">
                                    <Button variant="outline" size="sm">Doc.</Button>
                                </a>
                                <Button
                                    v-if="fatura.estado === 'pendente'"
                                    variant="outline"
                                    size="sm"
                                    @click="abrirModalComprovativo(fatura.id)"
                                >
                                    Marcar Paga
                                </Button>
                                <Link :href="route('faturas-fornecedor.edit', fatura.id)">
                                    <Button variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="destroy(fatura.id)">
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="faturas.data.length === 0">
                            <TableCell colspan="8" class="text-center text-muted-foreground py-8">
                                Nenhuma fatura encontrada.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex gap-1">
                <template v-for="link in faturas.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url"
                        class="px-3 py-1 text-sm rounded border"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'">
                        {{ decodeHtml(link.label) }}
                    </Link>
                    <span v-else class="px-3 py-1 text-sm rounded border text-muted-foreground cursor-not-allowed">
                        {{ decodeHtml(link.label) }}
                    </span>
                </template>
            </div>
        </div>

        <!-- Modal Comprovativo -->
        <div v-if="mostrarModalComprovativo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-background rounded-lg p-6 w-full max-w-md space-y-4 shadow-xl">
                <h2 class="text-lg font-semibold">Enviar Comprovativo de Pagamento</h2>
                <p class="text-sm text-muted-foreground">
                    Pretende enviar o comprovativo ao Fornecedor? Anexe o ficheiro abaixo.
                </p>
                <div class="space-y-1">
                    <Label>Comprovativo *</Label>
                    <Input type="file" @change="(e: Event) => ficheiroCoprovativo = (e.target as HTMLInputElement).files?.[0] ?? null" />
                </div>
                <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="mostrarModalComprovativo = false">Cancelar</Button>
                    <Button @click="enviarComprovativo" :disabled="!ficheiroCoprovativo">Enviar</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>