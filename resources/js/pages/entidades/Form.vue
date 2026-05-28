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
    tipo: 'cliente' | 'fornecedor';
    paises: Array<{ id: number; nome: string; codigo: string }>;
    entidade?: {
        id: number;
        nif: string;
        nome: string;
        morada: string | null;
        codigo_postal: string | null;
        localidade: string | null;
        pais_id: number | null;
        telefone: string | null;
        telemovel: string | null;
        website: string | null;
        email: string | null;
        rgpd: boolean;
        observacoes: string | null;
        ativo: boolean;
        is_cliente: boolean;
        is_fornecedor: boolean;
    };
}>();

const isEditing = !!props.entidade;
const titulo = isEditing
    ? `Editar ${props.tipo === 'cliente' ? 'Cliente' : 'Fornecedor'}`
    : `Novo ${props.tipo === 'cliente' ? 'Cliente' : 'Fornecedor'}`;

const form = useForm({
    is_cliente: Boolean(props.entidade?.is_cliente ?? props.tipo === 'cliente'),
    is_fornecedor: Boolean(props.entidade?.is_fornecedor ?? props.tipo === 'fornecedor'),
    nif: props.entidade?.nif ?? '',
    nome: props.entidade?.nome ?? '',
    morada: props.entidade?.morada ?? '',
    codigo_postal: props.entidade?.codigo_postal ?? '',
    localidade: props.entidade?.localidade ?? '',
    pais_id: props.entidade?.pais_id?.toString() ?? '',
    telefone: props.entidade?.telefone ?? '',
    telemovel: props.entidade?.telemovel ?? '',
    website: props.entidade?.website ?? '',
    email: props.entidade?.email ?? '',
    rgpd: props.entidade?.rgpd ?? false,
    observacoes: props.entidade?.observacoes ?? '',
    ativo: props.entidade?.ativo ?? true,
});

async function lookupVies() {
    console.log('VIES lookup para:', form.nif);
    
    if (!form.nif || form.nif.length < 9) {
        console.log('NIF muito curto:', form.nif?.length);
        
        return;
    }

    try {
        const res = await fetch(`/api/vies/${form.nif}`);
        console.log('Resposta status:', res.status);
        
        const data = await res.json();
        console.log('Dados recebidos:', data);

        if (data.nome) {
            form.nome = data.nome;
        }

        if (data.morada) {
            form.morada = data.morada;
        }
    } catch (e) {
        console.error('Erro VIES:', e);
    }
}

function submit() {
    if (isEditing) {
        form.put(route('entidades.update', props.entidade!.id));
    } else {
        form.post(route('entidades.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="titulo" />

        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ titulo }}</h1>
                <Link :href="route(tipo === 'cliente' ? 'clientes.index' : 'fornecedores.index')">
                    <Button class="cursor-pointer" variant="outline">Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Tipo -->
                <div class="flex gap-6">
                    <div class="flex items-center gap-2">
                        <Checkbox 
                            id="is_cliente" 
                            :model-value="form.is_cliente"
                            @update:modelValue="(val) => form.is_cliente = val === true"
                        />
                        <Label for="is_cliente">Cliente</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <Checkbox 
                            id="is_fornecedor"
                            :model-value="form.is_fornecedor"
                            @update:modelValue="(val) => form.is_fornecedor = val === true"
                        />
                        <Label for="is_fornecedor">Fornecedor</Label>
                    </div>
                </div>


                <!-- NIF -->
                <div class="space-y-1">
                    <Label for="nif">NIF *</Label>
                    <div class="flex gap-2">
                        <Input id="nif" v-model="form.nif" @blur="lookupVies" placeholder="999999999" />
                        <Button class="cursor-pointer" type="button" variant="outline" @click="lookupVies">VIES</Button>
                    </div>
                    <p v-if="form.errors.nif" class="text-sm text-destructive">{{ form.errors.nif }}</p>
                </div>

                <!-- Nome -->
                <div class="space-y-1">
                    <Label for="nome">Nome *</Label>
                    <Input id="nome" v-model="form.nome" />
                    <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                </div>

                <!-- Morada -->
                <div class="space-y-1">
                    <Label for="morada">Morada</Label>
                    <Input id="morada" v-model="form.morada" />
                </div>

                <!-- Código Postal + Localidade -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="codigo_postal">Código Postal</Label>
                        <Input id="codigo_postal" v-model="form.codigo_postal" placeholder="XXXX-XXX" />
                        <p v-if="form.errors.codigo_postal" class="text-sm text-destructive">{{ form.errors.codigo_postal }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="localidade">Localidade</Label>
                        <Input id="localidade" v-model="form.localidade" />
                    </div>
                </div>

                <!-- País -->
                <div class="space-y-1">
                    <Label>País</Label>
                    <Select v-model="form.pais_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Selecionar país" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="pais in paises" :key="pais.id" :value="pais.id.toString()">
                                {{ pais.nome }}
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

                <!-- Website + Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="website">Website</Label>
                        <Input id="website" v-model="form.website" type="url" />
                    </div>
                    <div class="space-y-1">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" />
                    </div>
                </div>

               <!-- RGPD -->
                <div class="flex items-center gap-2">
                    <Checkbox 
                        id="rgpd" 
                        :model-value="form.rgpd"
                        @update:modelValue="(val) => form.rgpd = val === true"
                    />
                    <Label for="rgpd">Consentimento RGPD</Label>
                </div>

                <!-- Observações -->
                <div class="space-y-1">
                    <Label for="observacoes">Observações</Label>
                    <Textarea id="observacoes" v-model="form.observacoes" rows="3" />
                </div>

                <!-- Estado -->
                <div class="flex items-center gap-2">
                    <Checkbox 
                        id="ativo" 
                        :model-value="form.ativo"
                        @update:modelValue="(val) => form.ativo = val === true"
                    />
                    <Label for="ativo">Ativo</Label>
                </div>

                <Button class="cursor-pointer" type="submit" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>