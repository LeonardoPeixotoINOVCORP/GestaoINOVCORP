<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
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

defineProps<{
    contactos: {
        data: Array<{
            id: number;
            numero: number;
            nome: string;
            apelido: string | null;
            telefone: string | null;
            telemovel: string | null;
            email: string | null;
            ativo: boolean;
            entidade: { id: number; nome: string } | null;
            funcao: { id: number; nome: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()


function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover este contacto?')) {
        router.delete(route('contactos.destroy', id));
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
        <Head title="Contactos" />

        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between ">
                <h1 class="text-2xl font-semibold">Contactos</h1>
                <Link :href="route('contactos.create')">
                    <Button class="cursor-pointer">Novo Contacto</Button>
                </Link>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nº</TableHead>
                            <TableHead>Nome</TableHead>
                            <TableHead>Apelido</TableHead>
                            <TableHead>Função</TableHead>
                            <TableHead>Entidade</TableHead>
                            <TableHead>Telefone</TableHead>
                            <TableHead>Telemóvel</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="contacto in contactos.data" :key="contacto.id">
                            <TableCell>{{ contacto.numero }}</TableCell>
                            <TableCell class="font-medium">{{ contacto.nome }}</TableCell>
                            <TableCell>{{ contacto.apelido ?? '—' }}</TableCell>
                            <TableCell>{{ contacto.funcao?.nome ?? '—' }}</TableCell>
                            <TableCell>{{ contacto.entidade?.nome ?? '—' }}</TableCell>
                            <TableCell>{{ contacto.telefone ?? '—' }}</TableCell>
                            <TableCell>{{ contacto.telemovel ?? '—' }}</TableCell>
                            <TableCell>{{ contacto.email ?? '—' }}</TableCell>
                            <TableCell>
                                <Badge class="rounded-lg py-2 px-3" :variant="contacto.ativo ? 'default' : 'secondary'">
                                    {{ contacto.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right space-x-2">
                                <Link :href="route('contactos.edit', contacto.id)">
                                    <Button 
                                    variant="outline" 
                                    size="sm"
                                    class="cursor-pointer"
                                    >Editar</Button>
                                </Link>
                                <Button 
                                variant="destructive" 
                                size="sm" 
                                @click="destroy(contacto.id)"
                                class="cursor-pointer"
                                >
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="contactos.data.length === 0">
                            <TableCell colspan="10" class="text-center text-muted-foreground py-8">
                                Nenhum contacto encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex gap-1">
                <template v-for="link in contactos.links" :key="link.label">
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