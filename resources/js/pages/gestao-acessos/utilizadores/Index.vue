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
    utilizadores: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            phone: string | null;
            roles: Array<{ id: number; name: string }>;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover este utilizador?')) {
        router.delete(route('gestao-acessos.utilizadores.destroy', id));
    }
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Utilizadores" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Utilizadores</h1>
                <Link :href="route('gestao-acessos.utilizadores.create')">
                    <Button class="cursor-pointer">Novo Utilizador</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Telemóvel</TableHead>
                            <TableHead>Grupo</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in utilizadores.data" :key="user.id">
                            <TableCell class="font-medium">{{ user.name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>{{ user.phone ?? '—' }}</TableCell>
                            <TableCell>
                                <Badge v-if="user.roles.length > 0" variant="secondary">
                                    {{ user.roles[0].name }}
                                </Badge>
                                <span v-else class="text-muted-foreground text-sm">—</span>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('gestao-acessos.utilizadores.edit', user.id)">
                                    <Button class="cursor-pointer" variant="outline" size="sm">Editar</Button>
                                </Link>
                                <Button class="cursor-pointer" variant="destructive" size="sm" @click="destroy(user.id)">
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="utilizadores.data.length === 0">
                            <TableCell colspan="5" class="text-center text-muted-foreground py-8">
                                Nenhum utilizador encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in utilizadores.links" :key="link.label">
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