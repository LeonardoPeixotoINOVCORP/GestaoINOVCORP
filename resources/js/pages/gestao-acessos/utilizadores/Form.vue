<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    grupos: Array<{ id: number; name: string }>;
    utilizador?: {
        id: number;
        name: string;
        email: string;
        phone: string | null;
        roles: Array<{ id: number; name: string }>;
    };
}>()

const isEditing = !!props.utilizador;

const form = useForm({
    name:     props.utilizador?.name ?? '',
    email:    props.utilizador?.email ?? '',
    phone:    props.utilizador?.phone ?? '',
    password: '',
    role:     props.utilizador?.roles[0]?.name ?? '',
});

function submit() {
    if (isEditing) {
        form.put(route('gestao-acessos.utilizadores.update', props.utilizador!.id));
    } else {
        form.post(route('gestao-acessos.utilizadores.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Utilizador' : 'Novo Utilizador'" />
        <div class="p-6 max-w-lg mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ isEditing ? 'Editar Utilizador' : 'Novo Utilizador' }}
                </h1>
                <Link :href="route('gestao-acessos.utilizadores.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1">
                    <Label for="name">Nome *</Label>
                    <Input id="name" v-model="form.name" />
                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-1">
                    <Label for="email">Email *</Label>
                    <Input id="email" v-model="form.email" type="email" />
                    <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-1">
                    <Label for="phone">Telemóvel</Label>
                    <Input id="phone" v-model="form.phone" />
                </div>

                <div class="space-y-1">
                    <Label for="password">
                        {{ isEditing ? 'Nova Password (deixar em branco para manter)' : 'Password *' }}
                    </Label>
                    <Input id="password" v-model="form.password" type="password" />
                    <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                </div>

                <div class="space-y-1">
                    <Label>Grupo de Permissões</Label>
                    <Select v-model="form.role">
                        <SelectTrigger><SelectValue placeholder="Selecionar grupo" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">— Nenhum —</SelectItem>
                            <SelectItem v-for="g in grupos" :key="g.id" :value="g.name">
                                {{ g.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>