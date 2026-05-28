<script setup lang="ts">
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background">
        <AppSidebarHeader :breadcrumbs="breadcrumbs" />
        <main class="flex-1 overflow-x-hidden">
            <slot />
        </main>
        <Toaster />
    </div>
</template>