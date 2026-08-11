<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import MonthNav from '@/Components/portal/MonthNav.vue';
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, tabStatusMeta } from '@/composables/useFormat';

const props = defineProps({
    month: { type: Object, required: true },
    summary: {
        type: Object,
        default: () => ({ charged: 0, open: 0, paid: 0, count: 0 }),
    },
    children: { type: Array, default: () => [] },
    selected_student_id: { type: Number, default: null },
    by_student: { type: Array, default: () => [] },
    entries: { type: Array, default: () => [] },
});

const monthHref = (key) => {
    const params = new URLSearchParams({ month: key });
    if (props.selected_student_id) {
        params.set('student_id', String(props.selected_student_id));
    }

    return `/parent/tab?${params.toString()}`;
};

const selectedChild = computed(() =>
    props.children.find((child) => child.id === props.selected_student_id) || null,
);
</script>

<template>
    <Head title="Fiado" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div>
                <h2 class="font-display text-[2rem] font-semibold leading-[1.08] tracking-tight text-ink">
                    Fiado
                </h2>
                <p class="mt-2 max-w-[34ch] text-sm leading-relaxed text-ink-soft/65">
                    Veja o que seus filhos pediram fiado em cada mês.
                </p>
            </div>

            <MonthNav :month="month" :href-for="monthHref" />

            <article class="relative overflow-hidden rounded-[1.7rem] bg-ink px-5 py-5 text-foam shadow-[0_20px_48px_rgba(20,36,31,0.22)]">
                <div class="pointer-events-none absolute -right-10 -top-12 size-40 rounded-full border-[28px] border-zest/10" />
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-foam/40">
                    {{ selectedChild ? selectedChild.name : 'Todos os filhos' }} · {{ month.label }}
                </p>
                <p class="font-display mt-3 text-[2.2rem] font-semibold leading-none text-zest">
                    {{ formatMoney(summary.charged) }}
                </p>
                <p class="mt-3 text-xs text-foam/55">
                    {{ summary.count }} {{ summary.count === 1 ? 'pedido' : 'pedidos' }}
                    <span v-if="summary.open"> · {{ formatMoney(summary.open) }} em aberto</span>
                </p>
            </article>

            <div v-if="children.length > 1" class="flex gap-2 overflow-x-auto pb-1">
                <Link
                    :href="`/parent/tab?month=${month.key}`"
                    class="shrink-0 rounded-full px-3.5 py-2 text-xs font-semibold"
                    :class="!selected_student_id ? 'bg-ink text-foam' : 'border border-line bg-white/70 text-ink-soft/70'"
                >
                    Todos
                </Link>
                <Link
                    v-for="child in children"
                    :key="child.id"
                    :href="`/parent/tab?month=${month.key}&student_id=${child.id}`"
                    class="shrink-0 rounded-full px-3.5 py-2 text-xs font-semibold"
                    :class="selected_student_id === child.id ? 'bg-ink text-foam' : 'border border-line bg-white/70 text-ink-soft/70'"
                >
                    {{ child.name }}
                </Link>
            </div>

            <section v-if="!selected_student_id && by_student.length">
                <h3 class="font-display mb-3 text-xl font-semibold text-ink">Por filho</h3>
                <div class="space-y-2">
                    <Link
                        v-for="row in by_student"
                        :key="row.id"
                        :href="`/parent/children/${row.id}/tab?month=${month.key}`"
                        class="flex items-center justify-between rounded-[1.2rem] border border-line bg-white/75 px-4 py-3.5"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink">{{ row.name }}</p>
                            <p class="mt-0.5 text-xs text-ink-soft/50">
                                {{ row.summary.count }} {{ row.summary.count === 1 ? 'pedido' : 'pedidos' }}
                                <span v-if="row.tab_balance"> · {{ formatMoney(row.tab_balance) }} em aberto</span>
                            </p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-ink">{{ formatMoney(row.summary.charged) }}</p>
                    </Link>
                </div>
            </section>

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
                                <span v-if="!selected_student_id && entry.student_name">{{ entry.student_name }} · </span>
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
