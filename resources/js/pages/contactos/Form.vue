<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    entidades: Array<{ id: number; nome: string; nif: string }>;
    funcoes: Array<{ id: number; nome: string }>;
    contacto?: {
        id: number;
        entidade_id: number;
        nome: string;
        apelido: string | null;
        funcao_id: number | null;
        telefone: string | null;
        telemovel: string | null;
        email: string | null;
        rgpd: boolean;
        observacoes: string | null;
        ativo: boolean;
    };
}>();

const isEditing = !!props.contacto;

const form = useForm({
    entidade_id: props.contacto?.entidade_id?.toString() ?? '',
    nome: props.contacto?.nome ?? '',
    apelido: props.contacto?.apelido ?? '',
    funcao_id: props.contacto?.funcao_id?.toString() ?? '',
    telefone: props.contacto?.telefone ?? '',
    telemovel: props.contacto?.telemovel ?? '',
    email: props.contacto?.email ?? '',
    rgpd: props.contacto?.rgpd ?? false,
    observacoes: props.contacto?.observacoes ?? '',
    ativo: props.contacto?.ativo ?? true,
});

function submit() {
    if (isEditing) {
        form.put(route('contactos.update', props.contacto!.id));
    } else {
        form.post(route('contactos.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Contacto' : 'Novo Contacto'" />

        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ isEditing ? 'Editar Contacto' : 'Novo Contacto' }}
                </h1>
                <Link :href="route('contactos.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Entidade -->
                <div class="space-y-1">
                    <Label>Entidade *</Label>
                    <Select v-model="form.entidade_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Selecionar entidade" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="entidade in entidades"
                                :key="entidade.id"
                                :value="entidade.id.toString()"
                            >
                                {{ entidade.nome }} ({{ entidade.nif }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.entidade_id" class="text-sm text-destructive">{{ form.errors.entidade_id }}</p>
                </div>

                <!-- Nome + Apelido -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="nome">Nome *</Label>
                        <Input id="nome" v-model="form.nome" />
                        <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="apelido">Apelido</Label>
                        <Input id="apelido" v-model="form.apelido" />
                    </div>
                </div>

                <!-- Função -->
                <div class="space-y-1">
                    <Label>Função</Label>
                    <Select v-model="form.funcao_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Selecionar função" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="funcao in funcoes"
                                :key="funcao.id"
                                :value="funcao.id.toString()"
                            >
                                {{ funcao.nome }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Telefone + Telemóvel -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="telefone">Telefone</Label>
                        <Input id="telefone" v-model="form.telefone" />
                    </div>
                    <div class="space-y-1">
                        <Label for="telemovel">Telemóvel</Label>
                        <Input id="telemovel" v-model="form.telemovel" />
                    </div>
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" />
                    <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                </div>

                <!-- RGPD -->
                <div class="flex items-center gap-2">
                    <Checkbox id="rgpd" v-model:checked="form.rgpd" />
                    <Label for="rgpd">Consentimento RGPD</Label>
                </div>

                <!-- Observações -->
                <div class="space-y-1">
                    <Label for="observacoes">Observações</Label>
                    <Textarea id="observacoes" v-model="form.observacoes" rows="3" />
                </div>

                <!-- Estado -->
                <div class="flex items-center gap-2">
                    <Checkbox id="ativo" v-model:checked="form.ativo" />
                    <Label for="ativo">Ativo</Label>
                </div>

                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>