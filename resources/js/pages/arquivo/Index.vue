<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    Table, TableBody, TableCell,
    TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();
defineProps<{
    ficheiros: {
        data: Array<{
            id: number;
            nome: string;
            tipo_mime: string | null;
            tamanho: number | null;
            created_at: string;
            entidade: { id: number; nome: string } | null;
            user: { id: number; name: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    entidades?: Array<{ id: number; nome: string }>;
}>()

const mostrarUpload = ref(false);

const form = useForm({
    nome:        '',
    ficheiro:    null as File | null,
    entidade_id: 'none',
    observacoes: '',
});

function submit() {
    form.post(route('arquivo.store'), {
        onSuccess: () => {
            mostrarUpload.value = false;
            form.reset();
        },
    });
}

function destroy(id: number) {
    if (confirm('Tem a certeza que pretende remover este ficheiro?')) {
        router.delete(route('arquivo.destroy', id));
    }
}

function formatBytes(bytes: number | null): string {
    if (!bytes) { 
        return '—'; 
    }

    if (bytes < 1024) { 
        return bytes + ' B'; 
    }
    
    if (bytes < 1024 * 1024) { 
        return (bytes / 1024).toFixed(1) + ' KB'; 
    }
    
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('pt-PT');
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Arquivo Digital" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Arquivo Digital</h1>
                <Button class="cursor-pointer" @click="mostrarUpload = !mostrarUpload">
                    {{ mostrarUpload ? 'Cancelar' : 'Carregar Ficheiro' }}
                </Button>
            </div>

            <!-- Upload Form -->
            <div v-if="mostrarUpload" class="rounded-md border p-4 space-y-4">
                <h2 class="font-medium">Novo Ficheiro</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="nome">Nome *</Label>
                            <Input id="nome" v-model="form.nome" />
                            <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label for="ficheiro">Ficheiro *</Label>
                            <Input id="ficheiro" type="file"
                                @change="(e: Event) => form.ficheiro = (e.target as HTMLInputElement).files?.[0] ?? null" />
                            <p v-if="form.errors.ficheiro" class="text-sm text-destructive">{{ form.errors.ficheiro }}</p>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Entidade (opcional)</Label>
                        <Select v-model="form.entidade_id">
                            <SelectTrigger><SelectValue placeholder="Selecionar entidade" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">— Nenhuma —</SelectItem>
                                <SelectItem v-for="e in entidades" :key="e.id" :value="e.id.toString()">
                                    {{ e.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button type="submit" class="cursor-pointer" :disabled="form.processing">Carregar</Button>
                </form>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nome</TableHead>
                            <TableHead>Tipo</TableHead>
                            <TableHead>Tamanho</TableHead>
                            <TableHead>Entidade</TableHead>
                            <TableHead>Utilizador</TableHead>
                            <TableHead>Data</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="ficheiro in ficheiros.data" :key="ficheiro.id">
                            <TableCell class="font-medium">{{ ficheiro.nome }}</TableCell>
                            <TableCell class="text-xs text-muted-foreground">{{ ficheiro.tipo_mime ?? '—' }}</TableCell>
                            <TableCell>{{ formatBytes(ficheiro.tamanho) }}</TableCell>
                            <TableCell>{{ ficheiro.entidade?.nome ?? '—' }}</TableCell>
                            <TableCell>{{ ficheiro.user?.name ?? '—' }}</TableCell>
                            <TableCell>{{ formatDate(ficheiro.created_at) }}</TableCell>
                            <TableCell class="text-right space-x-2">
                                <a :href="route('arquivo.download', ficheiro.id)" target="_blank">
                                    <Button variant="outline" size="sm">Download</Button>
                                </a>
                                <Button variant="destructive" size="sm" @click="destroy(ficheiro.id)">
                                    Remover
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="ficheiros.data.length === 0">
                            <TableCell colspan="7" class="text-center text-muted-foreground py-8">
                                Nenhum ficheiro encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex gap-1">
                <template v-for="link in ficheiros.links" :key="link.label">
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