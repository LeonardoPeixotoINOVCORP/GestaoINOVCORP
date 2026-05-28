<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Entrar" />

    <div class="min-h-screen flex">

        <!-- Painel esquerdo — decorativo -->
        <div class="hidden lg:flex lg:w-1/2 bg-zinc-950 relative overflow-hidden flex-col justify-between p-12">
            <!-- Grid pattern -->
            <div class="absolute inset-0 opacity-10"
                style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 40px 40px;" />

            <!-- Glow -->
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full opacity-20"
                style="background: radial-gradient(circle, #e629c3 0%, transparent 70%)" />

            <!-- Logo / marca -->
            <div class="relative z-10">
                <div class="text-white font-bold text-xl tracking-tight">Gestão - INOVCORP</div>
            </div>

            <!-- Texto central -->
            <div class="relative z-10 space-y-4">
                <h2 class="text-white text-4xl font-bold leading-tight tracking-tight">
                    Controlo total<br />do seu negócio.
                </h2>
                <p class="text-zinc-400 text-sm leading-relaxed max-w-xs">
                    Clientes, fornecedores, propostas, encomendas e financeiro num só lugar.
                </p>
            </div>

            <!-- Footer do painel -->
            <div class="relative z-10">
                <p class="text-zinc-600 text-xs">© {{ new Date().getFullYear() }} Gestão - INOVCORP. <br> Todos os direitos reservados.</p>
            </div>
        </div>

        <!-- Painel direito — formulário -->
        <div class="flex-1 flex items-center justify-center bg-background px-6 py-12">
            <div class="w-full max-w-sm space-y-8">

                <!-- Header -->
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight">Bem-vindo de volta</h1>
                    <p class="text-sm text-muted-foreground">Introduza as suas credenciais para entrar</p>
                </div>

                <!-- Status message -->
                <div v-if="status" class="rounded-md bg-green-500/10 border border-green-500/20 px-4 py-3 text-sm text-green-600">
                    {{ status }}
                </div>

                <!-- Form -->
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="email@exemplo.com"
                            class="h-10"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">Password</Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                                :tabindex="5"
                            >
                                Esqueceu a password?
                            </TextLink>
                        </div>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="h-10"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <Label for="remember" class="text-sm font-normal text-muted-foreground cursor-pointer">
                            Manter sessão iniciada
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="w-full h-10 cursor-pointer"
                        :tabindex="4"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'A entrar...' : 'Entrar' }}
                    </Button>
                </Form>

                <!-- Registo -->
                <p v-if="canRegister" class="text-center text-sm text-muted-foreground">
                    Não tem conta?
                    <TextLink :href="register()" :tabindex="6" class="font-medium text-foreground hover:underline">
                        Criar conta
                    </TextLink>
                </p>

            </div>
        </div>
    </div>
</template>