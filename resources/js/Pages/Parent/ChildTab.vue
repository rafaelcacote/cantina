<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import BackLink from '@/Components/portal/BackLink.vue';
import MonthNav from '@/Components/portal/MonthNav.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, tabStatusMeta } from '@/composables/useFormat';

const props = defineProps({
    child: { type: Object, required: true },
    month: { type: Object, required: true },
    summary: {
        type: Object,
        default: () => ({ charged: 0, open: 0, paid: 0, count: 0 }),
    },
    tab_balance: { type: Number, default: 0 },
    entries: { type: Array, default: () => [] },
});

const monthHref = (key) => `/parent/children/${props.child.id}/tab?month=${key}`;
</script>

<template>
    <Head :title="`Fiado · ${child.name}`" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <BackLink :href="`/parent/children/${child.id}`" :label="child.name" />
            </div>

            <div>
                <h2 class="font-display text-[2rem] font-semibold leading-[1.08] tracking-tight text-ink">
                    Fiado
                </h2>
                <p class="mt-2 max-w-[32ch] text-sm leading-relaxed text-ink-soft/65">
                    Tudo que {{ child.name }} pediu fiado, mês a mês.
                </p>
            </div>

            <MonthNav :month="month" :href-for="monthHref" />

            <article class="relative overflow-hidden rounded-[1.7rem] bg-ink px-5 py-5 text-foam shadow-[0_20px_48px_rgba(20,36,31,0.22)]">
                <div class="pointer-events-none absolute -right-10 -top-12 size-40 rounded-full border-[28px] border-zest/10" />
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-foam/40">
                    Em {{ month.label }}
                </p>
                <p class="font-display mt-3 text-[2.2rem] font-semibold leading-none text-zest">
                    {{ formatMoney(summary.charged) }}
                </p>
                <p class="mt-3 text-xs text-foam/55">
                    {{ summary.count }} {{ summary.count === 1 ? 'pedido' : 'pedidos' }} no fiado
                </p>
                <div class="relative mt-5 grid grid-cols-2 gap-3 border-t border-white/10 pt-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-foam/40">Neste mês</p>
                        <p class="mt-1 text-sm font-semibold">{{ formatMoney(summary.open) }} em aberto</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-foam/40">Total em aberto</p>
                        <p class="mt-1 text-sm font-semibold">{{ formatMoney(tab_balance) }}</p>
                    </div>
                </div>
            </article>

            <section>
                <h3 class="font-display mb-3 text-xl font-semibold text-ink">Lançamentos</h3>
                <div v-if="entries.length" class="space-y-2">
                    <component
                        :is="entry.order_id ? Link : 'div'"
                        v-for="entry in entries"
                        :key="entry.id"
                        :href="entry.order_id ? `/parent/orders/${entry.order_id}` : undefined"
                        class="flex items-center justify-between gap-3 rounded-[1.2rem] border border-line bg-white/75 px-4 py-3.5"
                        :class="entry.status === 'cancelled' ? 'opacity-55' : ''"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink">{{ entry.preview }}</p>
                            <p class="mt-0.5 text-xs text-ink-soft/50">
                                {{ tabStatusMeta(entry.status).label }} · {{ entry.entry_date }}
                            </p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-ink">{{ formatMoney(entry.amount) }}</p>
                    </component>
                </div>
                <p
                    v-else
                    class="rounded-[1.2rem] border border-dashed border-line px-4 py-8 text-center text-sm text-ink-soft/50"
                >
                    Nenhum fiado em {{ month.label.toLowerCase() }}.
                </p>
            </section>
        </section>
    </MobileShell>
</template>
