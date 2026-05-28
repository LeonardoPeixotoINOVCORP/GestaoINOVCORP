<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { computed, ref, reactive } from 'vue';
import { Button } from '@/components/ui/button';
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

const props = defineProps<{
    clientes: Array<{ id: number; nome: string; nif: string }>;
    fornecedores: Array<{ id: number; nome: string }>;
    artigos: Array<{ id: number; referencia: string; nome: string; preco: number; iva: number }>;
    proposta?: {
        id: number;
        entidade_id: number;
        validade: string | null;
        estado: string;
        observacoes: string | null;
        linhas: Array<{
            id: number;
            artigo_id: number;
            fornecedor_id: number | null;
            quantidade: number;
            preco_venda: number;
            preco_custo: number;
            iva: number;
        }>;
    };
}>()

const isEditing = !!props.proposta;

const form = useForm({
    entidade_id: props.proposta?.entidade_id?.toString() ?? '',
    validade:    props.proposta?.validade ?? '',
    estado:      props.proposta?.estado ?? 'rascunho',
    observacoes: props.proposta?.observacoes ?? '',
});

const linhas = reactive(
    props.proposta?.linhas.map(l => ({
        artigo_id:     l.artigo_id,
        fornecedor_id: l.fornecedor_id?.toString() ?? '',
        quantidade:    l.quantidade,
        preco_venda:   Number(l.preco_venda),
        preco_custo:   Number(l.preco_custo),
        iva:           Number(l.iva),
    })) ?? []
);

const pesquisa = ref('');
const mostrarResultados = ref(false);

const artigosFiltrados = computed(() => {
    if (!pesquisa.value) {
        return [];
    }

    const idsAdicionados = new Set(linhas.map(l => l.artigo_id));

    return props.artigos.filter(a =>
        !idsAdicionados.has(a.id) && (
            a.nome.toLowerCase().includes(pesquisa.value.toLowerCase()) ||
            a.referencia.toLowerCase().includes(pesquisa.value.toLowerCase())
        )
    );
});

function adicionarLinha(artigo: typeof props.artigos[0]) {
    linhas.push({
        artigo_id:     artigo.id,
        fornecedor_id: '',
        quantidade:    1,
        preco_venda:   Number(artigo.preco),
        preco_custo:   0,
        iva:           Number(artigo.iva),
    });
    pesquisa.value = '';
    mostrarResultados.value = false;
}

function removerLinha(index: number) {
    linhas.splice(index, 1);
}

function nomeArtigo(id: number): string {
    const artigo = props.artigos.find(a => a.id === id);

    return artigo ? `${artigo.referencia} — ${artigo.nome}` : '—';
}

const total = computed(() => {
    return linhas.reduce((acc, l) => {
        return acc + l.quantidade * l.preco_venda * (1 + l.iva / 100);
    }, 0).toFixed(2);
});

const erroLinhas = ref('');

function submit() {
    if (linhas.length === 0) {
        erroLinhas.value = 'Adiciona pelo menos um artigo.';

        return;
    }

    erroLinhas.value = '';

    form.transform((data) => ({
        ...data,
        linhas: linhas.map(l => ({
            artigo_id:     l.artigo_id,
            fornecedor_id: l.fornecedor_id ? Number(l.fornecedor_id) : null,
            quantidade:    l.quantidade,
            preco_venda:   l.preco_venda,
            preco_custo:   l.preco_custo,
            iva:           l.iva,
        })),
    }));

    if (isEditing) {
        form.put(route('propostas.update', props.proposta!.id));
    } else {
        form.post(route('propostas.store'));
    }
}

function esconderResultados() {
    setTimeout(() => {
        mostrarResultados.value = false;
    }, 200);
}

</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Editar Proposta' : 'Nova Proposta'" />
        <div class="p-6 max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">{{ isEditing ? 'Editar Proposta' : 'Nova Proposta' }}</h1>
                <Link :href="route('propostas.index')">
                    <Button variant="outline" class="cursor-pointer">Voltar</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- Cliente + Validade + Estado -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <Label>Cliente *</Label>
                        <Select v-model="form.entidade_id">
                            <SelectTrigger><SelectValue placeholder="Selecionar cliente" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in clientes" :key="c.id" :value="c.id.toString()">
                                    {{ c.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.entidade_id" class="text-sm text-destructive">{{ form.errors.entidade_id }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="validade">Validade</Label>
                        <Input id="validade" v-model="form.validade" type="date" />
                    </div>
                    <div class="space-y-1">
                        <Label>Estado</Label>
                        <Select v-model="form.estado">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="rascunho">Rascunho</SelectItem>
                                <SelectItem value="fechado">Fechado</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- Pesquisa de artigos -->
                <div class="space-y-2">
                    <Label>Adicionar Artigo</Label>
                    <div class="relative">
                        <Input
                            v-model="pesquisa"
                            placeholder="Pesquisar por referência ou nome..."
                            @focus="mostrarResultados = true"
                            @blur="esconderResultados"
                        />
                        <div
                            v-if="mostrarResultados && artigosFiltrados.length > 0"
                            class="absolute z-10 w-full rounded-md border bg-background shadow-md max-h-48 overflow-y-auto mt-1"
                        >
                            <button
                                v-for="artigo in artigosFiltrados"
                                :key="artigo.id"
                                type="button"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-muted transition-colors"
                                @mousedown.prevent="adicionarLinha(artigo)"
                            >
                                <span class="font-mono text-muted-foreground">{{ artigo.referencia }}</span>
                                — {{ artigo.nome }}
                                <span class="float-right text-muted-foreground">{{ Number(artigo.preco).toFixed(2) }} €</span>
                            </button>
                        </div>
                        <div
                            v-if="mostrarResultados && pesquisa && artigosFiltrados.length === 0"
                            class="absolute z-10 w-full rounded-md border bg-background shadow-md mt-1"
                        >
                            <div class="px-4 py-2 text-sm text-muted-foreground">Nenhum artigo encontrado.</div>
                        </div>
                    </div>
                </div>

                <!-- Linhas -->
                <div v-if="linhas.length > 0" class="rounded-md border overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-muted">
                            <tr>
                                <th class="text-left px-4 py-2">Artigo</th>
                                <th class="text-left px-4 py-2">Fornecedor</th>
                                <th class="text-left px-4 py-2 w-20">Qtd</th>
                                <th class="text-left px-4 py-2 w-28">Preço Venda</th>
                                <th class="text-left px-4 py-2 w-28">Preço Custo</th>
                                <th class="text-left px-4 py-2 w-20">IVA %</th>
                                <th class="text-left px-4 py-2 w-28">Total</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(linha, index) in linhas" :key="index">
                                <td class="px-4 py-2">{{ nomeArtigo(linha.artigo_id) }}</td>
                                <td class="px-4 py-2">
                                    <select
                                        v-model="linha.fornecedor_id"
                                        class="h-8 w-full rounded-md border border-input bg-background px-2 text-xs"
                                    >
                                        <option value="">— Nenhum —</option>
                                        <option v-for="f in fornecedores" :key="f.id" :value="f.id.toString()">
                                            {{ f.nome }}
                                        </option>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <Input v-model.number="linha.quantidade" type="number" min="1" class="h-8 w-16" />
                                </td>
                                <td class="px-4 py-2">
                                    <Input v-model.number="linha.preco_venda" type="number" step="0.01" min="0" class="h-8 w-24" />
                                </td>
                                <td class="px-4 py-2">
                                    <Input v-model.number="linha.preco_custo" type="number" step="0.01" min="0" class="h-8 w-24" />
                                </td>
                                <td class="px-4 py-2">
                                    <Input v-model.number="linha.iva" type="number" step="0.01" min="0" class="h-8 w-16" />
                                </td>
                                <td class="px-4 py-2 text-right whitespace-nowrap">
                                    {{ (linha.quantidade * linha.preco_venda * (1 + linha.iva / 100)).toFixed(2) }} €
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button
                                        type="button"
                                        class="text-destructive hover:text-destructive/80 font-bold"
                                        @click="removerLinha(index)"
                                    >✕</button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-muted">
                            <tr>
                                <td colspan="6" class="px-4 py-2 text-right font-medium">Total:</td>
                                <td class="px-4 py-2 font-bold">{{ total }} €</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p v-if="erroLinhas" class="text-sm text-destructive">{{ erroLinhas }}</p>

                <!-- Observações -->
                <div class="space-y-1">
                    <Label for="observacoes">Observações</Label>
                    <Textarea id="observacoes" v-model="form.observacoes" rows="3" />
                </div>

                <Button type="submit" class="cursor-pointer" :disabled="form.processing">
                    {{ isEditing ? 'Guardar alterações' : 'Criar' }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>