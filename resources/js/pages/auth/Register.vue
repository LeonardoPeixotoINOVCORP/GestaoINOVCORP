<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <Head title="Criar Conta" />

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
                    Comece hoje<br />sem complicações.
                </h2>
                <p class="text-zinc-400 text-sm leading-relaxed max-w-xs">
                    Crie a sua conta e tenha acesso imediato a todas as ferramentas de gestão.
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
                    <h1 class="text-2xl font-semibold tracking-tight">Criar conta</h1>
                    <p class="text-sm text-muted-foreground">Preencha os dados abaixo para criar a sua conta</p>
                </div>

                <!-- Form -->
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <div class="space-y-2">
                        <Label for="name">Nome completo</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            name="name"
                            placeholder="O seu nome"
                            class="h-10"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="email@exemplo.com"
                            class="h-10"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <PasswordInput
                            id="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="••••••••"
                            class="h-10"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirmar password</Label>
                        <PasswordInput
                            id="password_confirmation"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="••••••••"
                            class="h-10"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <Button
                        type="submit"
                        class="w-full h-10 cursor-pointer"
                        :tabindex="5"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        {{ processing ? 'A criar conta...' : 'Criar conta' }}
                    </Button>
                </Form>

                <!-- Login -->
                <p class="text-center text-sm text-muted-foreground">
                    Já tem conta?
                    <TextLink :href="login()" :tabindex="6" class="font-medium text-foreground hover:underline">
                        Entrar
                    </TextLink>
                </p>

            </div>
        </div>
    </div>
</template>