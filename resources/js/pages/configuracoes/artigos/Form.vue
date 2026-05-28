<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    artigo?: {
        id: number;
        referencia: string;
        nome: string;
        descricao: string | null;
        preco: number;
        iva: number;
        foto: string | null;
        observacoes: string | null;
        ativo: boolean;
    };
}>()

const isEditing = !!props.artigo;

const form = useForm({
    referencia:  props.artigo?.referencia ?? '',
    nome:        props.artigo?.nome ?? '',
    descricao:   props.artigo?.descricao ?? '',
    preco:       props.artigo?.preco ?? 0,
    iva:         props.artigo?.iva ?? 23,
    foto:        null as File | null,
    observacoes: props.artigo?.observacoes ?? '',
    ativo:       Boolean(props.artigo?.ativo ?? true),
});

function submit() {
    if (isEditing) {
        form.put(route('configuracoes.artigos.update', props.artigo!.id), {
            forceFormData: true,
        });
    } else {
        form.put(route('configuracoes.artigos.store'));
    }
}

</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Artigo' : 'Novo Artigo'" />
        <div class="p-6 max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ isEditing ? 'Editar Artigo' : 'Novo Artigo' }}</h1>
                <Link :href="route('configuracoes.artigos.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>
            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="referencia">Referência *</Label>
                        <Input id="referencia" v-model="form.referencia" />
                        <p v-if="form.errors.referencia" class="text-sm text-destructive">{{ form.errors.referencia }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="nome">Nome *</Label>
                        <Input id="nome" v-model="form.nome" />
                        <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                    </div>
                </div>
                <div class="space-y-1">
                    <Label for="descricao">Descrição</Label>
                    <Textarea id="descricao" v-model="form.descricao" rows="3" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label for="preco">Preço *</Label>
                        <Input id="preco" v-model="form.preco" type="number" step="0.01" min="0" />
                        <p v-if="form.errors.preco" class="text-sm text-destructive">{{ form.errors.preco }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="iva">IVA (%) *</Label>
                        <Input id="iva" v-model="form.iva" type="number" step="0.01" min="0" max="100" />
                        <p v-if="form.errors.iva" class="text-sm text-destructive">{{ form.errors.iva }}</p>
                    </div>
                </div>
                <div class="space-y-1">
                    <Label for="foto">Foto</Label>
                    <Input id="foto" type="file" accept="image/*"
                        @change="(e: Event) => form.foto = (e.target as HTMLInputElement).files?.[0] ?? null" />
                    <img
                    v-if="props.artigo?.foto"
                    :src="`/storage/${props.artigo.foto}`"
                    class="mt-2 w-24 h-24 rounded object-cover"
                    />
                </div>
                <div class="space-y-1">
                    <Label for="observacoes">Observações</Label>
                    <Textarea id="observacoes" v-model="form.observacoes" rows="3" />
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox
                        id="ativo"
                        :model-value="form.ativo"
                        @update:modelValue="(val) => form.ativo = val === true"
                    />
                    <Label for="ativo">Ativo</Label>
                </div>
                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>