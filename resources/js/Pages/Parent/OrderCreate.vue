<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    children: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Novo pedido" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink href="/parent/orders" label="Pedidos" />
                <h2 class="font-display mt-5 text-[2rem] font-semibold leading-none tracking-tight text-ink">
                    Pedir para quem?
                </h2>
                <p class="mt-2 max-w-[32ch] text-sm leading-relaxed text-ink-soft/60">
                    Escolha o filho para montar o pedido na cantina.
                </p>
            </div>

            <div v-if="children.length" class="space-y-3">
                <Link
                    v-for="child in children"
                    :key="child.id"
                    :href="`/parent/children/${child.id}/menu`"
                    class="flex items-center gap-3 rounded-[1.35rem] border border-line bg-white/80 p-3.5 backdrop-blur transition active:scale-[0.99]"
                >
                    <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-mist font-display text-lg font-semibold text-leaf-deep">
                        {{ child.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-ink">{{ child.name }}</p>
                        <p class="mt-0.5 truncate text-xs text-ink-soft/55">
                            {{ child.school || 'Escola' }}
                        </p>
                    </div>
                    <span class="flex size-9 items-center justify-center rounded-full bg-ink text-zest">
                        <i class="pi pi-arrow-right text-xs" />
                    </span>
                </Link>
            </div>

            <div
                v-else
                class="rounded-[1.35rem] border border-dashed border-line bg-white/50 px-5 py-10 text-center"
            >
                <p class="font-display text-lg font-semibold text-ink">Nenhum filho disponível</p>
                <p class="mx-auto mt-2 max-w-[28ch] text-sm text-ink-soft/60">
                    Cadastre um filho e aguarde a cantina confirmar o cadastro para fazer pedidos.
                </p>
                <Link
                    href="/parent/children"
                    class="mt-5 inline-flex h-11 items-center rounded-2xl bg-ink px-4 text-sm font-semibold text-foam"
                >
                    Ver filhos
                </Link>
            </div>
        </section>
    </MobileShell>
</template>
