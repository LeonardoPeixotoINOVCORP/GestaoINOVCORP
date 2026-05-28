<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    empresa?: {
        id?: number;
        nome: string;
        morada: string | null;
        codigo_postal: string | null;
        localidade: string | null;
        nif: string | null;
        telefone: string | null;
        email: string | null;
        website: string | null;
        logotipo: string | null;
    };
}>()

const form = useForm({
    nome:          props.empresa?.nome ?? '',
    morada:        props.empresa?.morada ?? '',
    codigo_postal: props.empresa?.codigo_postal ?? '',
    localidade:    props.empresa?.localidade ?? '',
    nif:           props.empresa?.nif ?? '',
    telefone:      props.empresa?.telefone ?? '',
    email:         props.empresa?.email ?? '',
    website:       props.empresa?.website ?? '',
    logotipo:      null as File | null,
});

function submit() {
    form.put(route('empresa.update'));
}
</script>

<template>
    <AppLayout>
        <Head title="Configurações — Empresa" />
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <h1 class="text-2xl font-semibold">Dados da Empresa</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <!-- Logotipo -->
                <div class="space-y-2">
                    <Label>Logotipo</Label>
                    <div class="flex items-center gap-4">
                        <img
                            v-if="empresa?.logotipo"
                            :src="`/storage/${empresa.logotipo}`"
                            class="h-16 w-auto rounded border object-contain"
                        />
                        <Input type="file" accept="image/*"
                            @change="(e: Event) => form.logotipo = (e.target as HTMLInputElement).files?.[0] ?? null" />
                    </div>
                </div>

                <!-- Nome + NIF -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="nome">Nome *</Label>
                        <Input id="nome" v-model="form.nome" />
                        <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="nif">NIF</Label>
                        <Input id="nif" v-model="form.nif" />
                    </div>
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

                <!-- Telefone + Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="telefone">Telefone</Label>
                        <Input id="telefone" v-model="form.telefone" />
                    </div>
                    <div class="space-y-1">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" />
                    </div>
                </div>

                <!-- Website -->
                <div class="space-y-1">
                    <Label for="website">Website</Label>
                    <Input id="website" v-model="form.website" type="url" />
                </div>

                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    Guardar
                </Button>
            </form>
        </div>
    </AppLayout>
</template>