<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useRoute } from '@/composables/useRoute';
import AppLayout from '@/layouts/AppLayout.vue';

const route = useRoute();
</script>

<template>
    <AppLayout>
        <Head title="Subscrição Expirada" />

        <div class="p-6 max-w-2xl mx-auto">
            <div class="rounded-lg border p-8 space-y-4 text-center">
                <h1 class="text-3xl font-bold">
                    Subscrição Expirada
                </h1>

                <p class="text-muted-foreground">
                    O acesso a este tenant encontra-se suspenso.
                    Renove a subscrição para continuar a utilizar a plataforma.
                </p>

                <Link :href="route('billing.index')">
                    <Button class="cursor-pointer">
                        Gerir Subscrição
                    </Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>