<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell,
    TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    encomendas: {
        data: Array<{
            id: number;
            numero: number;
            data_encomenda: string | null;
            estado: 'rascunho' | 'fechado';
            tipo: 'cliente' | 'fornecedor';
            entidade: { id: number; nome: string } | null;
            linhas: Array<{ quantidade: number; preco_venda: number; iva: number }>;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    tipo: 'cliente' | 'fornecedor';
}>()

const titulo = computed(() => props.tipo === 'cliente' ? 'Encomendas - Clientes' : 'Encomendas - Fornecedores');
const criarRoute = computed(() => props.tipo === 'cliente' ? 'encomendas.create' : 'encomendas.fornecedor.create');

function calcTotal(linhas: Array<{ quantidade: number; preco_venda: number; iva: number }>): string {
    return linhas.reduce((acc, l) => acc + l.quantidade * Number(l.preco_venda) * (1 + Number(l.iva) / 100), 0).toFixed(2);
}

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover esta encomenda?')) {
        router.delete(route('encomendas.destroy', id));
    }
}

function converterFornecedor(id: number) {
    if (confirm('Converter em encomendas de fornecedor? Serão criadas encomendas por cada fornecedor associado.')) {
        router.post(route('encomendas.converter-fornecedor', id));
    }
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}

function formatDate(date: string | null): string {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleDateString('pt-PT');
}
</script>

<template>
    <AppLayout>
        <Head :title="titulo" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ titulo }}</h1>
                <Link :href="route(criarRoute)">
                    <Button class="cursor-pointer">Nova Encomenda</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nº</TableHead>
                            <TableHead>Data</TableHead>
                            <TableHead>{{ tipo === 'cliente' ? 'Cliente' : 'Fornecedor' }}</TableHead>
                            <TableHead>Valor Total</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="encomenda in encomendas.data" :key="encomenda.id">
                            <TableCell class="font-mono">{{ encomenda.numero }}</TableCell>
                            <TableCell>{{ formatDate(encomenda.data_encomenda) }}</TableCell>
                            <TableCell>{{ encomenda.entidade?.nome ?? '—' }}</TableCell>
                            <TableCell>{{ calcTotal(encomenda.linhas) }} €</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="encomenda.estado === 'fechado' ? 'default' : 'secondary'">
                                    {{ encomenda.estado === 'fechado' ? 'Fechado' : 'Rascunho' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <a 
                                :href="route('encomendas.pdf', encomenda.id)" 
                                target="_blank"
                                >
                                    <Button 
                                    variant="outline" 
                                    size="sm"
                                    class="cursor-pointer"
                                    >
                                    PDF</Button>
                                </a>
                                
                                <Link 
                                :href="route('encomendas.edit', encomenda.id)"
                                >
                                    <Button 
                                    variant="outline" 
                                    size="sm"
                                    class="cursor-pointer"
                                    >
                                        Editar
                                    </Button>
                                </Link>
                                
                                <Button
                                    v-if="tipo === 'cliente' && encomenda.estado === 'fechado'"
                                    variant="outline"
                                    size="sm"
                                    class="cursor-pointer"
                                    @click="converterFornecedor(encomenda.id)"
                                >
                                    Fornecedor
                                </Button>

                                <Button 
                                variant="destructive" 
                                size="sm" 
                                @click="destroy(encomenda.id)" 
                                class="cursor-pointer"
                                >
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="encomendas.data.length === 0">
                            <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                                Nenhuma encomenda encontrada.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in encomendas.links" :key="link.label">
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
    </AppLayout>
</template>