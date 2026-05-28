<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell,
    TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

defineProps<{
    grupos: {
        data: Array<{
            id: number;
            name: string;
            users_count: number;
            permissions: Array<{ id: number; name: string }>;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover este grupo?')) {
        router.delete(route('gestao-acessos.permissoes.destroy', id));
    }
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Grupos de Permissões" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Grupos de Permissões</h1>
                <Link :href="route('gestao-acessos.permissoes.create')">
                    <Button class="cursor-pointer">Novo Grupo</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome do Grupo</TableHead>
                            <TableHead>Utilizadores</TableHead>
                            <TableHead>Permissões</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="grupo in grupos.data" :key="grupo.id">
                            <TableCell class="font-medium">{{ grupo.name }}</TableCell>
                            <TableCell>{{ grupo.users_count }}</TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ grupo.permissions.length }} permissões
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('gestao-acessos.permissoes.edit', grupo.id)">
                                    <Button variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button variant="destructive" size="sm" @click="destroy(grupo.id)">
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="grupos.data.length === 0">
                            <TableCell colspan="4" class="text-center text-muted-foreground py-8">
                                Nenhum grupo encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in grupos.links" :key="link.label">
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