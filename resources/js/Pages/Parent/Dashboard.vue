<script setup>
import MobileShell from '@/Layouts/MobileShell.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney, studentStatusMeta, timeGreeting } from '@/composables/useFormat';

defineProps({
    greeting: { type: String, required: true },
    children: { type: Array, default: () => [] },
    metrics: {
        type: Object,
        default: () => ({
            children_count: 0,
            total_balance: 0,
            open_orders: 0,
            open_tab: 0,
        }),
    },
});
</script>

<template>
    <Head title="Início" />

    <MobileShell role="parent">
        <section class="space-y-6">
            <div class="animate-[fadeRise_0.55s_ease-out]">
                <p class="text-sm font-medium text-ink-soft/70">{{ timeGreeting() }},</p>
                <h2 class="font-display mt-1 max-w-[16ch] text-[2rem] font-semibold leading-[1.1] tracking-tight text-ink">
                    {{ greeting }}<span class="text-leaf">.</span>
                </h2>
                <p class="mt-3 max-w-[34ch] text-sm leading-relaxed text-ink-soft/70">
                    Acompanhe saldos, fiado, pedidos e o cadastro dos seus filhos.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 animate-[fadeRise_0.7s_ease-out]">
                <article class="rounded-[1.35rem] bg-ink px-4 py-4 text-foam shadow-[0_18px_40px_rgba(20,36,31,0.22)]">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zest/80">Saldo total</p>
                    <p class="font-display mt-3 text-2xl font-semibold tracking-tight">
                        {{ formatMoney(metrics.total_balance) }}
                    </p>
                    <p class="mt-2 text-xs text-foam/55">Carteiras dos filhos</p>
                </article>

                <Link
                    href="/parent/orders"
                    class="rounded-[1.35rem] border border-line bg-white/75 px-4 py-4 backdrop-blur"
                >
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-soft/45">Hoje</p>
                    <p class="font-display mt-3 text-2xl font-semibold tracking-tight text-ink">
                        {{ metrics.open_orders }}
                    </p>
                    <p class="mt-2 text-xs text-ink-soft/55">Pedidos em andamento</p>
                </Link>

                <Link
                    href="/parent/tab"
                    class="col-span-2 flex items-center justify-between rounded-[1.35rem] border border-line bg-white/75 px-4 py-4 backdrop-blur"
                >
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-soft/45">Fiado em aberto</p>
                        <p class="font-display mt-2 text-2xl font-semibold tracking-tight text-ink">
                            {{ formatMoney(metrics.open_tab) }}
                        </p>
                        <p class="mt-1 text-xs text-ink-soft/55">Veja o que pediram em cada mês</p>
                    </div>
                    <span class="flex size-10 items-center justify-center rounded-full bg-mist text-leaf-deep">
                        <i class="pi pi-book text-sm" />
                    </span>
                </Link>
            </div>

            <section class="animate-[fadeRise_0.85s_ease-out]">
                <div class="mb-3 flex items-end justify-between">
                    <h3 class="font-display text-xl font-semibold text-ink">Seus filhos</h3>
                    <Link href="/parent/children" class="text-xs font-semibold text-leaf-deep">
                        {{ metrics.children_count }} vinculados
                    </Link>
                </div>

                <div v-if="children.length" class="space-y-3">
                    <Link
                        v-for="(child, index) in children"
                        :key="child.id"
                        :href="`/parent/children/${child.id}`"
                        class="group flex items-center gap-3 rounded-[1.35rem] border border-line bg-white/80 p-3.5 backdrop-blur transition hover:border-leaf/25 hover:bg-white"
                        :style="{ animationDelay: `${0.05 * index}s` }"
                    >
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-mist font-display text-lg font-semibold text-leaf-deep">
                            {{ child.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink">{{ child.name }}</p>
                            <p class="mt-0.5 truncate text-xs text-ink-soft/55">
                                {{ child.school || 'Escola' }} · {{ studentStatusMeta(child.status).label }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-leaf-deep">{{ formatMoney(child.balance) }}</p>
                            <p class="text-[11px] text-ink-soft/45">carteira</p>
                            <p v-if="child.tab_balance > 0" class="mt-1 text-[11px] font-semibold text-bloom">
                                {{ formatMoney(child.tab_balance) }} fiado
                            </p>
                        </div>
                    </Link>
                </div>

                <div
                    v-else
                    class="rounded-[1.35rem] border border-dashed border-line bg-white/50 px-5 py-10 text-center"
                >
                    <p class="font-display text-lg font-semibold text-ink">Nenhum filho vinculado</p>
                    <p class="mx-auto mt-2 max-w-[28ch] text-sm text-ink-soft/60">
                        Cadastre seus filhos para acompanhar saldo e pedidos.
                    </p>
                    <Link
                        href="/parent/children/create"
                        class="mt-5 inline-flex h-11 items-center rounded-2xl bg-ink px-4 text-sm font-semibold text-foam"
                    >
                        Cadastrar filho
                    </Link>
                </div>
            </section>
        </section>
    </MobileShell>
</template>
