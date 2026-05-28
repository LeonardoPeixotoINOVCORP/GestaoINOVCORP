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
    acoes: {
        data: Array<{ id: number; nome: string; ativo: boolean }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover esta ação?')) {
        router.delete(route('configuracoes.calendario-acoes.destroy', id));
    }
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Ações de Calendário" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Ações de Calendário</h1>
                <Link :href="route('configuracoes.calendario-acoes.create')">
                    <Button class="cursor-pointer">Nova Ação</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="acao in acoes.data" :key="acao.id">
                            <TableCell class="font-medium">{{ acao.nome }}</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="acao.ativo ? 'default' : 'secondary'">
                                    {{ acao.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('configuracoes.calendario-acoes.edit', acao.id)">
                                    <Button variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="destroy(acao.id)">Remover</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="acoes.data.length === 0">
                            <TableCell colspan="3" class="text-center text-muted-foreground py-8">
                                Nenhuma ação encontrada.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in acoes.links" :key="link.label">
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