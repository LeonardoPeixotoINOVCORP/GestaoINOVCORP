<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Table, TableBody, TableCell,
    TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

useRoute();

defineProps<{
    logs: {
        data: Array<{
            id: number;
            log_name: string;
            description: string;
            subject_type: string | null;
            event: string | null;
            created_at: string;
            causer: { id: number; name: string } | null;
            properties: Record<string, any>;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>()

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('pt-PT');
}

function formatTime(date: string): string {
    return new Date(date).toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
}

function formatSubject(type: string | null): string {
    if (!type) { 
        return '—'; 
    }

    return type.split('\\').pop() ?? type;
}

function decodeHtml(label: string): string {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <AppLayout>
        <Head title="Logs" />
        <div class="p-6 space-y-4">
            <h1 class="text-2xl font-semibold">Logs de Auditoria</h1>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Data</TableHead>
                            <TableHead>Hora</TableHead>
                            <TableHead>Utilizador</TableHead>
                            <TableHead>Módulo</TableHead>
                            <TableHead>Ação</TableHead>
                            <TableHead>Descrição</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="log in logs.data" :key="log.id">
                            <TableCell>{{ formatDate(log.created_at) }}</TableCell>
                            <TableCell>{{ formatTime(log.created_at) }}</TableCell>
                            <TableCell>{{ log.causer?.name ?? 'Sistema' }}</TableCell>
                            <TableCell>{{ formatSubject(log.subject_type) }}</TableCell>
                            <TableCell>
                                <span class="capitalize">{{ log.description }}</span>
                            </TableCell>
                            <TableCell class="text-muted-foreground text-xs">
                                {{ log.properties ? Object.entries(log.properties).map(([k, v]) => `${k}: ${v}`).join(' · ') : '—' }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="logs.data.length === 0">
                            <TableCell colspan="6" class="text-center text-muted-foreground py-8">
                                Nenhum registo encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex gap-1">
                <template v-for="link in logs.links" :key="link.label">
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