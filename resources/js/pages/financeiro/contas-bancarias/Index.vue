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
    contas: {
        data: Array<{ id: number; banco: string; iban: string; swift: string | null; titular: string; ativo: boolean }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover esta conta?')) {
        router.delete(route('contas-bancarias.destroy', id));
    }
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Contas Bancárias" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Contas Bancárias</h1>
                <Link :href="route('contas-bancarias.create')">
                    <Button class="cursor-pointer">Nova Conta</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Banco</TableHead>
                            <TableHead>IBAN</TableHead>
                            <TableHead>SWIFT</TableHead>
                            <TableHead>Titular</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="conta in contas.data" :key="conta.id">
                            <TableCell class="font-medium">{{ conta.banco }}</TableCell>
                            <TableCell class="font-mono text-sm">{{ conta.iban }}</TableCell>
                            <TableCell>{{ conta.swift ?? '—' }}</TableCell>
                            <TableCell>{{ conta.titular }}</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="conta.ativo ? 'default' : 'secondary'">
                                    {{ conta.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('contas-bancarias.edit', conta.id)">
                                    <Button variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="destroy(conta.id)">Remover</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="contas.data.length === 0">
                            <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                                Nenhuma conta encontrada.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in contas.links" :key="link.label">
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