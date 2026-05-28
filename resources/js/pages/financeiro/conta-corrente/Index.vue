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
    movimentos: {
        data: Array<{
            id: number;
            data_movimento: string;
            descricao: string;
            valor: number;
            tipo: 'debito' | 'credito';
            referencia: string | null;
            entidade: { id: number; nome: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover este movimento?')) {
        router.delete(route('conta-corrente.destroy', id));
    }
}

function formatDate(date: string): string {
    if (!date) {
        return '—';
    }

    const d = new Date(date);

    if(isNaN(d.getTime())) {
        return '—'; 
    }

    return d.toLocaleDateString('pt-PT');
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Conta Corrente Clientes" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Conta Corrente Clientes</h1>
                <Link :href="route('conta-corrente.create')">
                    <Button class="cursor-pointer">Novo Movimento</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Data</TableHead>
                            <TableHead>Cliente</TableHead>
                            <TableHead>Descrição</TableHead>
                            <TableHead>Referência</TableHead>
                            <TableHead>Tipo</TableHead>
                            <TableHead class="text-right">Valor</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="mov in movimentos.data" :key="mov.id">
                            <TableCell>{{ formatDate(mov.data_movimento) }}</TableCell>
                            <TableCell>{{ mov.entidade?.nome ?? '—' }}</TableCell>
                            <TableCell>{{ mov.descricao }}</TableCell>
                            <TableCell>{{ mov.referencia ?? '—' }}</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="mov.tipo === 'credito' ? 'default' : 'destructive'">
                                    {{ mov.tipo === 'credito' ? 'Crédito' : 'Débito' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right font-mono"
                                :class="mov.tipo === 'credito' ? 'text-green-600' : 'text-red-600'">
                                {{ mov.tipo === 'debito' ? '-' : '+' }}{{ Number(mov.valor).toFixed(2) }} €
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('conta-corrente.edit', mov.id)">
                                    <Button variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="destroy(mov.id)">Remover</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="movimentos.data.length === 0">
                            <TableCell colspan="7" class="text-center text-muted-foreground py-8">
                                Nenhum movimento encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in movimentos.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" class="px-3 py-1 text-sm rounded border"
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