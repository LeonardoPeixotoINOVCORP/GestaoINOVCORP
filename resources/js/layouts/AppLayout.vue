<script setup lang="ts">
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background">
        <AppSidebarHeader />
        <main class="flex-1 overflow-x-hidden">
            <slot />
        </main>
        <Toaster />
    </div>
</template>