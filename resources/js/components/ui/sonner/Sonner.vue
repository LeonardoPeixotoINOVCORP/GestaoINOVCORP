<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Toaster as SonnerPrimitive } from 'vue-sonner';
import 'vue-sonner/style.css';

const { appearance } = useAppearance();

import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const page = usePage();

watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.erro) {
            toast.error(
                typeof flash.erro === 'string'
                    ? flash.erro
                    : flash.erro.message
            );
        }

        if (flash?.sucesso) {
            toast.success(flash.sucesso);
        }
    },
    { deep: true }
);
</script>

<template>
    <SonnerPrimitive
        :theme="appearance"
        class="toaster group"
        position="bottom-right"
        :style="{
            '--normal-bg': 'var(--popover)',
            '--normal-text': 'var(--popover-foreground)',
            '--normal-border': 'var(--border)',
        }"
    />
</template>
