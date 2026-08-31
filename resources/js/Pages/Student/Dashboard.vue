<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, orderStatusMeta, timeGreeting } from '@/composables/useFormat';

const props = defineProps({
    portalRole: { type: String, default: 'student' },
    basePath: { type: String, default: '/student' },
    greeting: { type: String, required: true },
    student: {
        type: Object,
        default: () => ({
            name: '',
            school: null,
            balance: 0,
        }),
    },
    recentOrders: { type: Array, default: () => [] },
});

const hello = computed(() => timeGreeting());
const firstName = computed(() => (props.greeting || '').split(' ')[0] || props.greeting);
</script>

<template>
    <Head title="Início" />

    <MobileShell :role="portalRole">
        <section class="space-y-7">
            <div class="animate-[fadeRise_0.55s_ease-out]">
                <p class="text-sm font-medium text-ink-soft/55">{{ hello }}</p>
                <h2 class="font-display mt-1 text-[2.2rem] font-semibold leading-none tracking-tight text-ink">
                    {{ firstName }}<span class="text-leaf">.</span>
                </h2>
            </div>

            <article
                class="relative overflow-hidden rounded-[1.85rem] bg-ink p-5 text-foam shadow-[0_24px_54px_rgba(20,36,31,0.25)] animate-[fadeRise_0.7s_ease-out]"
            >
                <div class="pointer-events-none absolute -right-14 -top-16 size-48 rounded-full border-[32px] border-zest/10" />
                <div class="pointer-events-none absolute -bottom-20 right-14 size-40 rounded-full bg-leaf/30 blur-2xl" />

                <div class="relative flex items-start justify-between gap-4">
                    <div>
                        <p class="flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-foam/55">
                            <i class="pi pi-wallet text-zest" />
                            Saldo disponível
                        </p>
                        <p class="font-display mt-4 text-[2.35rem] font-semibold leading-none tracking-tight">
                            {{ formatMoney(student.balance) }}
                        </p>
                        <p class="mt-3 max-w-[24ch] truncate text-xs text-foam/45">
                            {{ student.school || 'Sua escola' }}
                        </p>
                    </div>
                </div>

                <Link
                    :href="`${basePath}/menu`"
                    class="relative mt-5 flex items-center justify-between rounded-[1.25rem] bg-zest px-4 py-3.5 text-ink transition active:scale-[0.99]"
                >
                    <span>
                        <span class="block text-[10px] font-bold uppercase tracking-[0.15em] text-ink/55">Cardápio aberto</span>
                        <span class="mt-0.5 block font-semibold">Escolher meu lanche</span>
                    </span>
                    <span class="flex size-9 items-center justify-center rounded-full bg-ink text-zest">
                        <i class="pi pi-arrow-right text-xs" />
                    </span>
                </Link>
            </article>

            <section class="animate-[fadeRise_0.95s_ease-out]">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Atividade</p>
                        <h3 class="font-display mt-1 text-xl font-semibold text-ink">Pedidos recentes</h3>
                    </div>
                    <Link
                        v-if="recentOrders.length"
                        :href="`${basePath}/orders`"
                        class="flex size-9 items-center justify-center rounded-full border border-line bg-white/70 text-leaf-deep"
                        title="Ver todos"
                    >
                        <i class="pi pi-arrow-right text-xs" />
                    </Link>
                </div>

                <div v-if="recentOrders.length" class="overflow-hidden rounded-[1.45rem] border border-line bg-white/65 px-4 backdrop-blur">
                    <Link
                        v-for="order in recentOrders"
                        :key="order.id"
                        :href="`${basePath}/orders/${order.id}`"
                        class="flex items-center gap-3 border-b border-line py-3.5 last:border-0 transition active:opacity-60"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-2xl"
                            :class="orderStatusMeta(order.status).active
                                ? 'bg-zest/70 text-ink'
                                : 'bg-mist text-ink-soft/45'"
                        >
                            <i :class="orderStatusMeta(order.status).icon" class="text-xs" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink">{{ orderStatusMeta(order.status).hint }}</p>
                            <p class="mt-0.5 text-[11px] text-ink-soft/45">#{{ order.id }} · {{ order.created_at }}</p>
                        </div>
                        <p class="ml-auto shrink-0 text-sm font-semibold text-ink">{{ formatMoney(order.total) }}</p>
                    </Link>
                </div>

                <div
                    v-else
                    class="rounded-[1.55rem] border border-dashed border-line bg-white/40 px-5 py-9 text-center"
                >
                    <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-mist text-leaf-deep">
                        <i class="pi pi-shopping-bag" />
                    </span>
                    <p class="font-display mt-3 text-lg font-semibold text-ink">Nada por aqui ainda</p>
                    <p class="mx-auto mt-1.5 max-w-[28ch] text-sm text-ink-soft/60">
                        Seu primeiro pedido da cantina aparece nesta lista.
                    </p>
                </div>
            </section>
        </section>
    </MobileShell>
</template>
