<script setup lang="ts">
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';
import FullCalendar from '@fullcalendar/vue3';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

defineProps<{
    tipos: Array<{ id: number; nome: string; cor: string }>;
    acoes: Array<{ id: number; nome: string }>;
    entidades: Array<{ id: number; nome: string }>;
    utilizadores: Array<{ id: number; name: string }>;
}>()

const mostrarModal = ref(false);
const eventoAtual = ref<any>(null);
const filtroUser = ref('all');
const filtroEntidade = ref('all');
const eventos = ref<any[]>([]);


async function carregarEventos() {
    const params = new URLSearchParams();
    
    if (filtroUser.value && filtroUser.value !== 'all') {
        params.append('user_id', filtroUser.value);
    }

    
    if (filtroEntidade.value && filtroEntidade.value !== 'all') { 
        params.append('entidade_id', filtroEntidade.value); 
    }

    const res = await fetch(`/calendario/eventos?${params.toString()}`);
    eventos.value = await res.json();
}

onMounted(carregarEventos);

const form = useForm({
    titulo:      '',
    inicio:      '',
    fim:         '',
    duracao:     60,
    entidade_id: '',
    tipo_id:     '',
    acao_id:     '',
    partilhado:  false,
    descricao:   '',
    estado:      'pendente',
});

const calendarOptions = ref({
    plugins:     [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    locale:      'pt',
    headerToolbar: {
        left:   'prev,next today',
        center: 'title',
        right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
    },
    editable:    true,
    selectable:  true,
    events:      eventos,
    select(info: any) {
        form.reset();
        form.inicio = formatDateTimeLocal(info.start);

        if (info.end) {
            form.fim = formatDateTimeLocal(info.end);
        }
        
        eventoAtual.value = null;
        mostrarModal.value = true;
    },
    eventClick(info: any) {
        eventoAtual.value = info.event;
        form.titulo      = info.event.title;
        form.inicio = formatDateTimeLocal(info.event.start);
        form.fim = info.event.end ? formatDateTimeLocal(info.event.end) : '';
        form.descricao   = info.event.extendedProps.descricao ?? '';
        form.estado      = info.event.extendedProps.estado ?? 'pendente';
        form.partilhado  = info.event.extendedProps.partilhado ?? false;
        mostrarModal.value = true;
    },
});

function submit() {
    if (form.entidade_id === 'none') {
        form.entidade_id = '';
    }

    if (form.tipo_id === 'none') {
        form.tipo_id = '';
    }

    if (form.acao_id === 'none') {
        form.acao_id = '';
    }

    if (eventoAtual.value) {
        form.put(route('calendario.update', eventoAtual.value.id), {
            onSuccess: () => {
                mostrarModal.value = false;
                carregarEventos();
            },
        });
    } else {
        form.post(route('calendario.store'), {
            onSuccess: () => {
                mostrarModal.value = false;
                carregarEventos();
            },
        });
    }
}

function eliminarEvento() {
    if (!eventoAtual.value) { 
        return; 
    }
    
    if (confirm('Tem a certeza que pretende remover este evento?')) {
        form.delete(route('calendario.destroy', eventoAtual.value.id), {
            onSuccess: () => {
                mostrarModal.value = false;
                carregarEventos();
            },
        });
    }
}

function formatDateTimeLocal(date: Date) {
    const pad = (n: number) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}
</script>

<template>
    <AppLayout>
        <Head title="Calendário" />
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Calendário</h1>
            </div>

            <!-- Filtros -->
            <div class="flex gap-4">
                <div class="w-48">
                    <Select v-model="filtroUser" @update:modelValue="carregarEventos">
                        <SelectTrigger>
                            <SelectValue placeholder="Todos os utilizadores" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos os utilizadores</SelectItem>
                            <SelectItem v-for="u in utilizadores" :key="u.id" :value="u.id.toString()">
                                {{ u.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="w-48">
                    <Select v-model="filtroEntidade" @update:modelValue="carregarEventos">
                        <SelectTrigger>
                            <SelectValue placeholder="Todas as entidades" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas as entidades</SelectItem>
                            <SelectItem v-for="e in entidades" :key="e.id" :value="e.id.toString()">
                                {{ e.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="mx-auto max-w-5xl">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>

        <!-- Modal Evento -->
        <div v-if="mostrarModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-background rounded-lg p-6 w-full max-w-lg space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ eventoAtual ? 'Editar Evento' : 'Novo Evento' }}</h2>
                    <button @click="mostrarModal = false" class="cursor-pointer text-muted-foreground hover:text-foreground">✕</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1">
                        <Label>Título *</Label>
                        <Input v-model="form.titulo" />
                        <p v-if="form.errors.titulo" class="text-sm text-destructive">{{ form.errors.titulo }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Início *</Label>
                            <Input v-model="form.inicio" type="datetime-local" />
                        </div>
                        <div class="space-y-1">
                            <Label>Fim</Label>
                            <Input v-model="form.fim" type="datetime-local" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label>Duração (minutos)</Label>
                        <Input v-model.number="form.duracao" type="number" min="0" />
                    </div>

                    <div class="space-y-1">
                        <Label>Entidade</Label>
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

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Tipo</Label>
                            <Select v-model="form.tipo_id">
                                <SelectTrigger><SelectValue placeholder="Tipo" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">— Nenhum —</SelectItem>
                                    <SelectItem v-for="t in tipos" :key="t.id" :value="t.id.toString()">
                                        {{ t.nome }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Ação</Label>
                            <Select v-model="form.acao_id">
                                <SelectTrigger><SelectValue placeholder="Ação" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">— Nenhuma —</SelectItem>
                                    <SelectItem v-for="a in acoes" :key="a.id" :value="a.id.toString()">
                                        {{ a.nome }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label>Estado</Label>
                        <Select v-model="form.estado">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pendente">Pendente</SelectItem>
                                <SelectItem value="concluido">Concluído</SelectItem>
                                <SelectItem value="cancelado">Cancelado</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="partilhado"
                            :model-value="form.partilhado"
                            @update:modelValue="(val) => form.partilhado = val === true"
                        />
                        <Label for="partilhado">Partilhado com a equipa</Label>
                    </div>

                    <div class="space-y-1">
                        <Label>Descrição</Label>
                        <Textarea v-model="form.descricao" rows="3" />
                    </div>

                    <div class="flex justify-between">
                        <Button
                            v-if="eventoAtual"
                            type="button"
                            variant="destructive"
                            @click="eliminarEvento"
                            class="cursor-pointer"
                        >
                            Remover
                        </Button>
                        <div class="flex gap-2 ml-auto">
                            <Button class="cursor-pointer" type="button" variant="outline" @click="mostrarModal = false">Cancelar</Button>
                            <Button class="cursor-pointer" type="submit" :disabled="form.processing">Guardar</Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>