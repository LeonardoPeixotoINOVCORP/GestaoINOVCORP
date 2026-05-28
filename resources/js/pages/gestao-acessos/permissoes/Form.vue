<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();

const props = defineProps<{
    menus: string[];
    acoes: string[];
    grupo?: {
        id: number;
        name: string;
        permissions: Array<{ id: number; name: string }>;
    };
}>()

const isEditing = !!props.grupo;

const permissoesIniciais = props.grupo?.permissions.map(p => p.name) ?? [];

const form = useForm({
    nome:       props.grupo?.name ?? '',
    permissoes: permissoesIniciais,
});

function togglePermissao(nome: string) {
    const idx = form.permissoes.indexOf(nome);
    
    if (idx === -1) {
        form.permissoes.push(nome);
    } else {
        form.permissoes.splice(idx, 1);
    }
}

function toggleMenu(menu: string) {
    const todas = props.acoes.map(a => `${a}_${menu}`);
    const todasMarcadas = todas.every(p => form.permissoes.includes(p));

    if (todasMarcadas) {
        todas.forEach(p => {
            const idx = form.permissoes.indexOf(p);
            
            if (idx !== -1) { 
                form.permissoes.splice(idx, 1); 
            }
        });
    } else {
        todas.forEach(p => {
            if (!form.permissoes.includes(p)) { 
                form.permissoes.push(p); 
            }
        });
    }
}

function menuTodosMarcado(menu: string): boolean {
    return props.acoes.every(a => form.permissoes.includes(`${a}_${menu}`));
}

function temPermissao(acao: string, menu: string): boolean {
    return form.permissoes.includes(`${acao}_${menu}`);
}

const labelAcao: Record<string, string> = {
    create: 'Criar',
    read:   'Ver',
    update: 'Editar',
    delete: 'Remover',
};

const labelMenu: Record<string, string> = {
    'clientes':             'Clientes',
    'fornecedores':         'Fornecedores',
    'contactos':            'Contactos',
    'propostas':            'Propostas',
    'encomendas':           'Encomendas',
    'encomendas-fornecedor': 'Enc. Fornecedor',
    'faturas-fornecedor':   'Faturas',
    'arquivo':              'Arquivo',
    'contas-bancarias':     'Contas Bancárias',
    'conta-corrente':       'Conta Corrente',
    'calendario':           'Calendário',
    'utilizadores':         'Utilizadores',
    'permissoes':           'Permissões',
    'configuracoes':        'Configurações',
};

function submit() {
    if (isEditing) {
        form.put(route('gestao-acessos.permissoes.update', props.grupo!.id));
    } else {
        form.post(route('gestao-acessos.permissoes.store'));
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Grupo' : 'Novo Grupo'" />
        <div class="p-6 max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ isEditing ? 'Editar Grupo' : 'Novo Grupo de Permissões' }}
                </h1>
                <Link :href="route('gestao-acessos.permissoes.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="space-y-1">
                    <Label for="nome">Nome do Grupo *</Label>
                    <Input id="nome" v-model="form.nome" class="max-w-sm" />
                    <p v-if="form.errors.nome" class="text-sm text-destructive">{{ form.errors.nome }}</p>
                </div>

                <!-- Tabela de permissões -->
                <div class="rounded-md border overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-muted">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium">Menu</th>
                                <th class="text-center px-4 py-3 font-medium w-24"
                                    v-for="acao in acoes" :key="acao">
                                    {{ labelAcao[acao] ?? acao }}
                                </th>
                                <th class="text-center px-4 py-3 font-medium w-20">Todos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="menu in menus" :key="menu" class="hover:bg-muted/50">
                                <td class="px-4 py-3 font-medium">
                                    {{ labelMenu[menu] ?? menu }}
                                </td>
                                <td class="px-4 py-3 text-center" v-for="acao in acoes" :key="acao">
                                    <Checkbox
                                        :model-value="temPermissao(acao, menu)"
                                        @update:modelValue="() => togglePermissao(`${acao}_${menu}`)"
                                    />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Checkbox
                                        :model-value="menuTodosMarcado(menu)"
                                        @update:modelValue="() => toggleMenu(menu)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>