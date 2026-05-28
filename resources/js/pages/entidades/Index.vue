<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    entidades: {
        data: Array<{
            id: number;
            numero: number;
            nif: string;
            nome: string;
            telefone: string | null;
            telemovel: string | null;
            website: string | null;
            email: string | null;
            ativo: boolean;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    tipo: 'cliente' | 'fornecedor';
}>();

const titulo = computed(() => props.tipo === 'cliente' ? 'Clientes' : 'Fornecedores');
const criarRoute = computed(() => props.tipo === 'cliente' ? 'clientes.create' : 'fornecedores.create');

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover esta entidade?')) {
        router.delete(route('entidades.destroy', id));
    }
}

function decodeHtml(label: string): string {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head :title="titulo" />

        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ titulo }}</h1>
                <Link :href="route(criarRoute)">
                    <Button class="cursor-pointer">Novo {{ tipo === 'cliente' ? 'Cliente' : 'Fornecedor' }}</Button>
                </Link>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nº</TableHead>
                            <TableHead>NIF</TableHead>
                            <TableHead>Nome</TableHead>
                            <TableHead>Telefone</TableHead>
                            <TableHead>Telemóvel</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Website</TableHead>
                            <TableHead class="text-center">Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="entidade in entidades.data" :key="entidade.id">
                            <TableCell>{{ entidade.numero }}</TableCell>
                            <TableCell>{{ entidade.nif }}</TableCell>
                            <TableCell class="font-medium">{{ entidade.nome }}</TableCell>
                            <TableCell>{{ entidade.telefone ?? '—' }}</TableCell>
                            <TableCell>{{ entidade.telemovel ?? '—' }}</TableCell>
                            <TableCell>{{ entidade.email ?? '—' }}</TableCell>
                            <TableCell>{{ entidade.website ?? '—' }}</TableCell>
                            <TableCell class="text-center">
                                <Badge class="rounded-lg py-2 px-3" :variant="entidade.ativo ? 'default' : 'secondary'">
                                    {{ entidade.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('entidades.edit', entidade.id)">
                                    <Button 
                                    variant="outline" 
                                    size="sm"
                                    class="cursor-pointer"
                                    >
                                    Editar</Button>
                                </Link>
                                <Button 
                                variant="destructive" 
                                size="sm" 
                                @click="destroy(entidade.id)"
                                class="cursor-pointer"
                                >
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="entidades.data.length === 0">
                            <TableCell colspan="9" class="text-center text-muted-foreground py-8">
                                Nenhuma entidade encontrada.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex gap-1">
                <template v-for="link in entidades.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3 py-1 text-sm rounded border"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                    >
                        {{ decodeHtml(link.label) }}
                    </Link>
                    <span
                        v-else
                        class="px-3 py-1 text-sm rounded border text-muted-foreground cursor-not-allowed"
                    >
                        {{ decodeHtml(link.label) }}
                    </span>
                </template>
            </div>
        </div>
    </AppLayout>
</template>