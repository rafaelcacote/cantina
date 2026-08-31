<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, orderStatusMeta } from '@/composables/useFormat';

const props = defineProps({
    portalRole: { type: String, default: 'student' },
    basePath: { type: String, default: '/student' },
    orders: { type: Array, default: () => [] },
});

const toneClass = {
    bloom: 'bg-bloom/15 text-bloom',
    leaf: 'bg-leaf/15 text-leaf-deep',
    zest: 'bg-zest/80 text-ink',
    mist: 'bg-mist text-ink-soft/55',
    muted: 'bg-mist text-ink-soft/40',
};

const accentClass = {
    bloom: 'bg-bloom',
    leaf: 'bg-leaf',
    zest: 'bg-zest',
    mist: 'bg-line',
    muted: 'bg-line',
};

const openOrders = computed(() =>
    props.orders.filter((order) => orderStatusMeta(order.status).active),
);

const pastOrders = computed(() =>
    props.orders.filter((order) => !orderStatusMeta(order.status).active),
);

const itemLabel = (order) => {
    if (!order.item_count) {
        return order.created_at;
    }

    const count = `${order.item_count} ${order.item_count === 1 ? 'item' : 'itens'}`;

    return order.preview ? `${count} · ${order.preview}` : count;
};
</script>

<template>
    <Head title="Pedidos" />

    <MobileShell :role="portalRole">
        <section class="space-y-7">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="font-display text-[2rem] font-semibold leading-[1.08] tracking-tight text-ink">
                        Pedidos
                    </h2>
                    <p class="mt-2 max-w-[28ch] text-sm leading-relaxed text-ink-soft/65">
                        Acompanhe o que a cantina já recebeu de você.
                    </p>
                </div>

                <Link
                    :href="`${basePath}/menu`"
                    class="mt-1 flex size-11 shrink-0 items-center justify-center rounded-full bg-ink text-zest shadow-[0_12px_24px_rgba(20,36,31,0.18)] transition hover:bg-ink/90 active:scale-[0.96]"
                    title="Novo pedido"
                >
                    <i class="pi pi-plus text-sm" />
                </Link>
            </div>

            <template v-if="orders.length">
                <section v-if="openOrders.length" class="space-y-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-ink-soft/45">
                        Em andamento
                    </p>

                    <Link
                        v-for="order in openOrders"
                        :key="order.id"
                        :href="`${basePath}/orders/${order.id}`"
                        class="portal-card relative block overflow-hidden rounded-[1.5rem] pl-1.5 transition active:scale-[0.99]"
                    >
                        <span
                            class="absolute inset-y-3 left-1.5 w-1 rounded-full"
                            :class="accentClass[orderStatusMeta(order.status).tone]"
                        />

                        <div class="flex items-stretch gap-3 py-4 pr-4 pl-4">
                            <span
                                class="mt-0.5 flex size-11 shrink-0 items-center justify-center rounded-2xl"
                                :class="toneClass[orderStatusMeta(order.status).tone]"
                            >
                                <i :class="orderStatusMeta(order.status).icon" class="text-sm" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-display text-lg font-semibold leading-tight text-ink">
                                            {{ orderStatusMeta(order.status).hint }}
                                        </p>
                                        <p class="mt-1 truncate text-xs text-ink-soft/55">
                                            {{ itemLabel(order) }}
                                        </p>
                                    </div>
                                    <p class="shrink-0 font-display text-base font-semibold text-ink">
                                        {{ formatMoney(order.total) }}
                                    </p>
                                </div>
                                <p class="mt-2 text-[11px] text-ink-soft/40">
                                    #{{ order.id }} · {{ order.created_at }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </section>

                <section v-if="pastOrders.length" class="space-y-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-ink-soft/45">
                        Anteriores
                    </p>

                    <Link
                        v-for="order in pastOrders"
                        :key="order.id"
                        :href="`${basePath}/orders/${order.id}`"
                        class="flex items-center gap-3 rounded-[1.25rem] px-1 py-2.5 transition hover:bg-white/50 active:scale-[0.99]"
                    >
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-2xl"
                            :class="toneClass[orderStatusMeta(order.status).tone]"
                        >
                            <i :class="orderStatusMeta(order.status).icon" class="text-xs" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink">
                                {{ orderStatusMeta(order.status).label }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-ink-soft/50">
                                #{{ order.id }} · {{ order.created_at }}
                            </p>
                        </div>

                        <p class="shrink-0 text-sm font-semibold text-ink-soft/80">
                            {{ formatMoney(order.total) }}
                        </p>
                    </Link>
                </section>
            </template>

            <div
                v-else
                class="rounded-[1.7rem] border border-dashed border-line bg-white/45 px-6 py-12 text-center"
            >
                <span class="mx-auto flex size-16 items-center justify-center rounded-[1.35rem] bg-zest/70 text-ink">
                    <i class="pi pi-shopping-bag text-xl" />
                </span>
                <p class="font-display mt-5 text-xl font-semibold text-ink">Nada por aqui</p>
                <p class="mx-auto mt-2 max-w-[26ch] text-sm leading-relaxed text-ink-soft/60">
                    Seu primeiro pedido da cantina aparece nesta tela.
                </p>
                <Link
                    :href="`${basePath}/menu`"
                    class="mt-6 inline-flex items-center gap-2 rounded-full bg-ink py-2 pl-2 pr-4 text-sm font-semibold text-foam"
                >
                    <span class="flex size-7 items-center justify-center rounded-full bg-zest text-ink">
                        <i class="pi pi-plus text-[11px]" />
                    </span>
                    Fazer um pedido
                </Link>
            </div>
        </section>
    </MobileShell>
</template>
