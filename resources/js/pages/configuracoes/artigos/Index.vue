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
    artigos: {
        data: Array<{
            id: number;
            referencia: string;
            nome: string;
            descricao: string | null;
            preco: number;
            iva: number;
            foto: string | null;
            ativo: boolean;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover este artigo?')) {
        router.delete(route('configuracoes.artigos.destroy', id));
    }
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Artigos" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Artigos</h1>
                <Link :href="route('configuracoes.artigos.create')">
                    <Button class="cursor-pointer">Novo Artigo</Button>
                </Link>
            </div>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Foto</TableHead>
                            <TableHead>Referência</TableHead>
                            <TableHead>Nome</TableHead>
                            <TableHead>Descrição</TableHead>
                            <TableHead>Preço</TableHead>
                            <TableHead>IVA</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="artigo in artigos.data" :key="artigo.id">
                            <TableCell>
                                <img
                                    v-if="artigo.foto"
                                    :src="`/storage/${artigo.foto}`"
                                    class="w-10 h-10 rounded object-cover"
                                />
                                <div v-else class="w-10 h-10 rounded bg-muted flex items-center justify-center text-muted-foreground text-xs">
                                    —
                                </div>
                            </TableCell>
                            <TableCell class="font-mono">{{ artigo.referencia }}</TableCell>
                            <TableCell class="font-medium">{{ artigo.nome }}</TableCell>
                            <TableCell>{{ artigo.descricao ?? '—' }}</TableCell>
                            <TableCell>{{ Number(artigo.preco).toFixed(2) }} €</TableCell>
                            <TableCell>{{ artigo.iva }}%</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="artigo.ativo ? 'default' : 'secondary'">
                                    {{ artigo.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('configuracoes.artigos.edit', artigo.id)">
                                    <Button 
                                    variant="outline" 
                                    size="sm"
                                    class="cursor-pointer"
                                    >
                                        Editar
                                    </Button>
                                </Link>
                                <Button 
                                variant="destructive" 
                                size="sm" 
                                @click="destroy(artigo.id)"
                                class="cursor-pointer"
                                >
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="artigos.data.length === 0">
                            <TableCell colspan="8" class="text-center text-muted-foreground py-8">
                                Nenhum artigo encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div class="flex gap-1">
                <template v-for="link in artigos.links" :key="link.label">
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