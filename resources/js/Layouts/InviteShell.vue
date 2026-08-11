<script setup>
defineProps({
    tenant: {
        type: Object,
        default: () => ({ name: 'Cantina', logo_url: null }),
    },
});
</script>

<template>
    <div class="portal-atmosphere portal-grain relative min-h-dvh overflow-x-hidden">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 opacity-70" aria-hidden="true">
            <div class="absolute -left-16 -top-10 size-56 rounded-full bg-leaf/15 blur-3xl" />
            <div class="absolute right-0 top-8 size-48 rounded-full bg-zest/25 blur-3xl" />
        </div>

        <div class="relative mx-auto flex min-h-dvh w-full max-w-lg flex-col px-4 pb-[max(2rem,env(safe-area-inset-bottom))] pt-[max(1.25rem,env(safe-area-inset-top))] sm:px-6">
            <header class="mb-8 flex items-center gap-3">
                <div class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-[0.95rem] border border-white/70 bg-white/80 p-0.5 shadow-[0_8px_24px_rgba(20,36,31,0.07)] backdrop-blur-xl">
                    <img
                        v-if="tenant?.logo_url"
                        :src="tenant.logo_url"
                        :alt="tenant.name"
                        class="size-full object-contain"
                    >
                    <span
                        v-else
                        class="font-display text-lg font-semibold tracking-tight text-ink"
                    >
                        {{ (tenant?.name || 'C').charAt(0).toUpperCase() }}
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-ink-soft/45">
                        Convite
                    </p>
                    <h1 class="font-display mt-1 truncate text-[1.15rem] font-semibold leading-none tracking-tight text-ink">
                        {{ tenant?.name || 'Cantina' }}
                    </h1>
                </div>
            </header>

            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>
