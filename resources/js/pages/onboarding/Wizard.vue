<script setup lang="ts">
import { useForm, Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { useRoute } from '@/composables/useRoute';

const route = useRoute();

const props = defineProps<{
    tenant: any;
    step: number;
    checklist: Record<string, boolean>;
    percentagem: number;
    grupos: Array<{ id: number; name: string }>;
}>()

const checklistLabels: Record<string, string> = {
    empresa:    'Dados da empresa preenchidos',
    pais:       'Pelo menos um país configurado',
    artigo:     'Pelo menos um artigo criado',
    utilizador: 'Pelo menos um utilizador convidado',
    cliente:    'Pelo menos um cliente criado',
};

const todosCompletos = computed(() =>
    Object.values(props.checklist).every(Boolean)
);

const step1Form = useForm({
    nome:     props.tenant.nome || '',
    email:    props.tenant.email || '',
    telefone: props.tenant.telefone || '',
    website:  props.tenant.website || '',
});

const step2Form = useForm({
    nif:           props.tenant.nif || '',
    morada:        props.tenant.morada || '',
    codigo_postal: props.tenant.codigo_postal || '',
    localidade:    props.tenant.localidade || '',
});

const step3Form = useForm({
    logotipo: null as File | null,
});

const conviteForm = useForm({
    name:  '',
    email: '',
    role:  '',
});

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    step3Form.logotipo = target.files?.[0] ?? null;
}

function submitStep1() {
    step1Form.post(route('onboarding.store', { passo: 1 }), {
        preserveScroll: true,
    });
}

function submitStep2() {
    step2Form.post(route('onboarding.store', { passo: 2 }), {
        preserveScroll: true,
    });
}

function submitStep3() {
    step3Form.post(route('onboarding.store', { passo: 3 }), {
        preserveScroll: true,
        forceFormData: true,
    });
}

function enviarConvite() {
    conviteForm.post(route('convites.store'), {
        preserveScroll: true,
        onSuccess: () => conviteForm.reset(),
    });
}

function complete() {
    router.post(route('onboarding.complete'));
}
</script>

<template>
    
    <Head title="Configuração inicial" />


    <div class="min-h-screen flex items-center ">
        <div class="w-full max-w-4xl mx-auto py-8 px-4 space-y-8">

            <!-- Progresso geral -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold">Configuração inicial</h1>
                    <span class="text-sm text-muted-foreground">{{ percentagem }}% completo</span>
                </div>
                <div class="h-2 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all duration-500"
                        :style="{ width: `${percentagem}%` }" />
                </div>
            </div>

            <!-- Checklist -->
            <div class="rounded-lg border p-4 space-y-3">
                <h2 class="font-medium text-sm">Checklist de configuração</h2>
                <div v-for="(completo, key) in checklist" :key="key"
                    class="flex items-center gap-3 text-sm">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
                        :class="completo ? 'bg-green-500' : 'bg-muted border'">
                        <span v-if="completo" class="text-white text-xs">✓</span>
                    </div>
                    <span :class="completo ? 'text-foreground' : 'text-muted-foreground'">
                        {{ checklistLabels[key] ?? key }}
                    </span>
                </div>
            </div>

            <!-- Steps -->
            <div class="flex gap-2 mb-4">
                <div v-for="i in 4" :key="i"
                    class="flex-1 h-1 rounded-full"
                    :class="step > i ? 'bg-primary' : step === i ? 'bg-primary/60' : 'bg-muted'" />
            </div>

            <!-- Passo 1: Dados da empresa -->
            <form v-if="step === 1" @submit.prevent="submitStep1" class="space-y-4">
                <h2 class="text-xl font-semibold">Dados da empresa</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label>Nome da empresa *</Label>
                        <Input v-model="step1Form.nome" required />
                        <p v-if="step1Form.errors.nome" class="text-sm text-destructive">{{ step1Form.errors.nome }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Email</Label>
                        <Input v-model="step1Form.email" type="email" />
                    </div>
                    <div class="space-y-1">
                        <Label>Telefone</Label>
                        <Input v-model="step1Form.telefone" />
                    </div>
                    <div class="space-y-1">
                        <Label>Website</Label>
                        <Input v-model="step1Form.website" type="url" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <Button type="submit" :disabled="step1Form.processing">Próximo →</Button>
                </div>
            </form>

            <!-- Passo 2: Morada e NIF -->
            <form v-if="step === 2" @submit.prevent="submitStep2" class="space-y-4">
                <h2 class="text-xl font-semibold">Morada e fiscalidade</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label>NIF</Label>
                        <Input v-model="step2Form.nif" />
                    </div>
                    <div class="space-y-1">
                        <Label>Morada</Label>
                        <Input v-model="step2Form.morada" />
                    </div>
                    <div class="space-y-1">
                        <Label>Código Postal</Label>
                        <Input v-model="step2Form.codigo_postal" placeholder="XXXX-XXX" />
                    </div>
                    <div class="space-y-1">
                        <Label>Localidade</Label>
                        <Input v-model="step2Form.localidade" />
                    </div>
                </div>
                <div class="flex justify-between">
                    <Button type="button" variant="outline" @click="router.post(route('onboarding.back'))">← Anterior</Button>
                    <Button type="submit" :disabled="step2Form.processing">Próximo →</Button>
                </div>
            </form>

            <!-- Passo 3: Logotipo -->
            <form v-if="step === 3" @submit.prevent="submitStep3" class="space-y-4">
                <h2 class="text-xl font-semibold">Logotipo</h2>
                <div class="space-y-1">
                    <Label>Logotipo (opcional)</Label>
                    <Input type="file" accept="image/*" @change="onFileChange" />
                    <p v-if="step3Form.errors.logotipo" class="text-sm text-destructive">{{ step3Form.errors.logotipo }}</p>
                </div>
                <div class="flex justify-between">
                    <Button type="button" variant="outline" @click="router.post(route('onboarding.back'))">← Anterior</Button>
                    <Button type="submit" :disabled="step3Form.processing">Próximo →</Button>
                </div>
            </form>

            <!-- Passo 4: Convidar utilizadores -->
            <div v-if="step === 4" class="space-y-4">
                <h2 class="text-xl font-semibold">Convidar utilizadores</h2>
                <p class="text-sm text-muted-foreground">
                    Convida a tua equipa. Podes saltar este passo e convidar mais tarde em Gestão → Utilizadores.
                </p>
                <form @submit.prevent="enviarConvite" class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <Label>Nome *</Label>
                            <Input v-model="conviteForm.name" />
                            <p v-if="conviteForm.errors.name" class="text-sm text-destructive">{{ conviteForm.errors.name }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label>Email *</Label>
                            <Input v-model="conviteForm.email" type="email" />
                            <p v-if="conviteForm.errors.email" class="text-sm text-destructive">{{ conviteForm.errors.email }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label>Grupo</Label>
                            <Select v-model="conviteForm.role">
                                <SelectTrigger><SelectValue placeholder="Selecionar" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">— Nenhum —</SelectItem>
                                    <SelectItem v-for="g in grupos" :key="g.id" :value="g.name">
                                        {{ g.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <Button type="submit" variant="outline" :disabled="conviteForm.processing">
                        Enviar convite
                    </Button>
                </form>

                <div class="flex justify-between pt-4">
                    <Button
                    type="button"
                    variant="outline"
                    @click="router.post(route('onboarding.back'))"
                    >
                        Anterior
                    </Button>
                    <Button @click="complete" :class="todosCompletos ? '' : 'opacity-80'">
                        {{ todosCompletos ? 'Concluir configuração' : 'Saltar e ir para o dashboard →' }}
                    </Button>
                </div>
            </div>

        </div>
    </div>
</template>