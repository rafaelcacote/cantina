<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, orderStatusMeta } from '@/composables/useFormat';

const props = defineProps({
    orders: { type: Array, default: () => [] },
    children: { type: Array, default: () => [] },
    canOrderForSelf: { type: Boolean, default: true },
});

const openOrders = computed(() =>
    props.orders.filter((order) => orderStatusMeta(order.status).active),
);

const pastOrders = computed(() =>
    props.orders.filter((order) => !orderStatusMeta(order.status).active),
);

const canCreate = computed(() => props.children.length > 0 || props.canOrderForSelf);
</script>

<template>
    <Head title="Pedidos" />

    <MobileShell role="parent">
        <section class="space-y-7">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-display text-[2rem] font-semibold leading-[1.08] tracking-tight text-ink">
                        Pedidos
                    </h2>
                    <p class="mt-2 max-w-[30ch] text-sm leading-relaxed text-ink-soft/65">
                        Acompanhe o que seus filhos pediram — ou peça você mesmo.
                    </p>
                </div>
                <div v-if="canCreate" class="mt-1 flex shrink-0 gap-2">
                    <Link
                        href="/parent/self"
                        class="flex size-11 items-center justify-center rounded-full border border-line bg-white/80 text-leaf-deep"
                        title="Pedir para mim"
                    >
                        <i class="pi pi-user text-sm" />
                    </Link>
                    <Link
                        v-if="children.length"
                        href="/parent/orders/create"
                        class="flex size-11 items-center justify-center rounded-full bg-ink text-zest shadow-[0_12px_24px_rgba(20,36,31,0.18)] transition hover:bg-ink/90 active:scale-[0.96]"
                        title="Pedido para filho"
                    >
                        <i class="pi pi-plus text-sm" />
                    </Link>
                </div>
            </div>

            <section v-if="openOrders.length">
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Em andamento</p>
                <div class="space-y-2.5">
                    <Link
                        v-for="order in openOrders"
                        :key="order.id"
                        :href="`/parent/orders/${order.id}`"
                        class="block rounded-[1.25rem] border border-line bg-white/80 px-4 py-3.5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-ink">{{ order.student_name || 'Aluno' }}</p>
                                <p class="mt-0.5 text-xs text-ink-soft/50">
                                    {{ orderStatusMeta(order.status).label }} · {{ order.created_at }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold text-ink">{{ formatMoney(order.total) }}</p>
                        </div>
                    </Link>
                </div>
            </section>

            <section>
                <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-ink-soft/40">Anteriores</p>
                <div v-if="pastOrders.length" class="space-y-2.5">
                    <Link
                        v-for="order in pastOrders"
                        :key="order.id"
                        :href="`/parent/orders/${order.id}`"
                        class="block rounded-[1.25rem] border border-line bg-white/70 px-4 py-3.5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-ink">{{ order.student_name || 'Aluno' }}</p>
                                <p class="mt-0.5 text-xs text-ink-soft/50">
                                    {{ orderStatusMeta(order.status).label }} · {{ order.created_at }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold text-ink">{{ formatMoney(order.total) }}</p>
                        </div>
                    </Link>
                </div>
                <div
                    v-else-if="!openOrders.length"
                    class="rounded-[1.35rem] border border-dashed border-line px-5 py-10 text-center"
                >
                    <p class="text-sm text-ink-soft/55">Nenhum pedido dos seus filhos ainda.</p>
                    <Link
                        v-if="children.length"
                        href="/parent/orders/create"
                        class="mt-5 inline-flex h-11 items-center rounded-2xl bg-ink px-4 text-sm font-semibold text-foam"
                    >
                        Fazer um pedido
                    </Link>
                </div>
            </section>
        </section>
    </MobileShell>
</template>
