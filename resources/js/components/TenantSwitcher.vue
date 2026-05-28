<script setup lang="ts">
import { router, Link, usePage } from '@inertiajs/vue3'
import { Building2, ChevronDown, Check, Plus } from 'lucide-vue-next'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from '@/composables/useRoute'

const route = useRoute()

const { props } = usePage()

const tenants = props.tenants as Array<{
    id: number
    nome: string
    slug: string
}> || []

const currentTenant = props.tenant as {
    id: number | string
    nome: string
} | null || null

const open = ref(false)

function switchTenant(tenantId: number) {
    open.value = false

    router.post(
        route('tenant.switch', {
            tenant: tenantId,
        }),
        {
            preserveScroll: true,
        },
        {
            onSuccess: () => {
                window.location.reload()
            },
        }
    )
}

/* fechar dropdown ao clicar fora */

const dropdownRef = ref<HTMLElement | null>(null)

function handleClickOutside(event: MouseEvent) {
    if (
        dropdownRef.value &&
        !dropdownRef.value.contains(event.target as Node)
    ) {
        open.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            @click="open = !open"
            class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium hover:bg-accent transition-colors"
        >
            <Building2 class="h-4 w-4" />

            <span class="max-w-[150px] truncate">
                {{ currentTenant?.nome || 'Select tenant' }}
            </span>

            <ChevronDown
                class="h-4 w-4 transition-transform"
                :class="{ 'rotate-180': open }"
            />
        </button>

        <div
            v-if="open"
            class="absolute left-0 mt-2 w-56 rounded-md border bg-popover shadow-lg z-50"
        >
            <div class="p-1">
                <div
                    v-for="tenant in tenants"
                    :key="tenant.id"
                    @click="switchTenant(tenant.id)"
                    class="flex cursor-pointer items-center justify-between rounded-sm px-2 py-1.5 text-sm transition-colors hover:bg-accent"
                    :class="{
                        'bg-accent font-medium':
                            Number(currentTenant?.id) === Number(tenant.id)
                    }"
                >
                    <span class="truncate">
                        {{ tenant.nome }}
                    </span>

                    <Check
                        v-if="Number(currentTenant?.id) === Number(tenant.id)"
                        class="h-4 w-4"
                    />
                </div>

                <hr 
                    v-if="$page.props.auth.user.can_create_tenants"
                    class="my-1" 
                />

                <Link
                    v-if="$page.props.auth.user.can_create_tenants"
                    :href="route('tenant.create')"
                    class="flex items-center gap-2 rounded-sm px-2 py-1.5 text-sm transition-colors hover:bg-accent"
                >
                    <Plus class="h-4 w-4" />
                    Criar novo tenant
                </Link>
            </div>
        </div>
    </div>
</template>