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
    tipos: {
        data: Array<{ id: number; nome: string; cor: string; ativo: boolean }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover este tipo?')) {
        router.delete(route('configuracoes.calendario-tipos.destroy', id));
    }
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Tipos de Calendário" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Tipos de Calendário</h1>
                <Link :href="route('configuracoes.calendario-tipos.create')">
                    <Button class="cursor-pointer">Novo Tipo</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Cor</TableHead>
                            <TableHead>Nome</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="tipo in tipos.data" :key="tipo.id">
                            <TableCell>
                                <div class="w-6 h-6 rounded-full" :style="{ backgroundColor: tipo.cor }" />
                            </TableCell>
                            <TableCell class="font-medium">{{ tipo.nome }}</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="tipo.ativo ? 'default' : 'secondary'">
                                    {{ tipo.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('configuracoes.calendario-tipos.edit', tipo.id)">
                                    <Button variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="destroy(tipo.id)">Remover</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="tipos.data.length === 0">
                            <TableCell colspan="4" class="text-center text-muted-foreground py-8">
                                Nenhum tipo encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in tipos.links" :key="link.label">
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