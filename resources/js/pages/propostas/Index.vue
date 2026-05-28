<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell,
    TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

defineProps<{
    propostas: {
        data: Array<{
            id: number;
            numero: number;
            data_proposta: string | null;
            validade: string | null;
            estado: 'rascunho' | 'fechado';
            entidade: { id: number; nome: string } | null;
            linhas: Array<{ quantidade: number; preco_venda: number; iva: number }>;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function calcTotal(linhas: Array<{ quantidade: number; preco_venda: number; iva: number }>): string {
    const total = linhas.reduce((acc, l) => acc + l.quantidade * Number(l.preco_venda) * (1 + Number(l.iva) / 100), 0);
    
    return total.toFixed(2);
}

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover esta proposta?')) {
        router.delete(route('propostas.destroy', id));
    }
}

function converter(id: number) {
    if (confirm('Converter esta proposta em encomenda?')) {
        router.post(route('propostas.converter', id));
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
        <Head title="Propostas" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Propostas</h1>
                <Link :href="route('propostas.create')">
                    <Button class="cursor-pointer">Nova Proposta</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nº</TableHead>
                            <TableHead>Data</TableHead>
                            <TableHead>Validade</TableHead>
                            <TableHead>Cliente</TableHead>
                            <TableHead>Valor Total</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="proposta in propostas.data" :key="proposta.id">
                            <TableCell class="font-mono">{{ proposta.numero }}</TableCell>
                            <TableCell>{{ formatDate(proposta.data_proposta) }}</TableCell>
                            <TableCell>{{ formatDate(proposta.validade) }}</TableCell>
                            <TableCell>{{ proposta.entidade?.nome ?? '—' }}</TableCell>
                            <TableCell>{{ calcTotal(proposta.linhas) }} €</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="proposta.estado === 'fechado' ? 'default' : 'secondary'">
                                    {{ proposta.estado === 'fechado' ? 'Fechado' : 'Rascunho' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <a :href="route('propostas.pdf', proposta.id)" target="_blank">
                                    <Button variant="outline" size="sm">PDF</Button>
                                </a>
                                <Link :href="route('propostas.edit', proposta.id)">
                                    <Button variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button variant="outline" size="sm" @click="converter(proposta.id)">
                                    Encomenda
                                </Button>
                                <Button variant="destructive" size="sm" @click="destroy(proposta.id)">
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="propostas.data.length === 0">
                            <TableCell colspan="7" class="text-center text-muted-foreground py-8">
                                Nenhuma proposta encontrada.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in propostas.links" :key="link.label">
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